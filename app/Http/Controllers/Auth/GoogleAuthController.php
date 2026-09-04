<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        // "prompt=select_account" obliga a Google a mostrar el selector de
        // cuentas siempre, en vez de reconectar automáticamente con la
        // última cuenta de Google ya autenticada en el navegador.
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request, PHPMailerService $mailer)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Error al autenticar con Google: ' . $e->getMessage());
            return redirect('/auth.php')->withErrors(['error' => 'Error al autenticar con Google.']);
        }

        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            // Obtener el rol predeterminado, ej: usuario
            $rol = Role::where('nombre', 'usuario')->first();
            
            $nameParts = explode(' ', $googleUser->name);
            
            // Crear usuario
            $user = User::create([
                'nombre' => $nameParts[0],
                'apellido' => count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '',
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => Hash::make(Str::random(16)), // Contraseña aleatoria
                'rol_id' => $rol ? $rol->id : 1, // Ajustar según ID real
                'activo' => true,
            ]);
        } elseif (!$user->google_id) {
            // Actualizar si ya existe pero sin google_id
            $user->google_id = $googleUser->id;
            $user->save();
        }

        // Generar código de verificación 2FA para Google
        $verificationCode = strtoupper(Str::random(6));
        $user->verification_code = $verificationCode;
        $user->save();

        // Enviar correo con código
        $view = view('emails.google-verification', ['code' => $verificationCode, 'user' => $user])->render();
        $mailer->sendEmail($user->email, 'Código de verificación - ' . config('app.name'), $view);

        // Guardar email en sesión para la vista de verificación
        $request->session()->put('verify_email', $user->email);

        return redirect()->route('google.verify.view');
    }

    public function showVerifyView(Request $request)
    {
        if (!$request->session()->has('verify_email')) {
            return redirect('/auth.php');
        }

        return view('auth.verify-google', ['email' => $request->session()->get('verify_email')]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->verification_code === strtoupper($request->code)) {
            // Código correcto
            $user->verification_code = null;
            $user->save();

            Auth::login($user);
            
            $request->session()->forget('verify_email');
            $request->session()->regenerate();

            return redirect()->intended('/dashboard.php');
        }

        return back()->withErrors(['code' => 'El código ingresado es incorrecto.']);
    }
}

