<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\Hash;

class CustomPasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.custom-forgot-password');
    }

    public function sendResetLinkEmail(Request $request, PHPMailerService $mailer)
    {
        $request->validate(['email' => 'required|email|exists:usuarios,email']);

        $token = Str::random(64);

        // Guardar token en password_resets
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token), // En la DB se guarda hasheado según estándar Laravel
                'created_at' => Carbon::now()
            ]
        );

        $resetLink = route('custom.password.reset', ['token' => $token, 'email' => $request->email]);

        // Renderizar vista
        $user = User::where('email', $request->email)->first();
        $view = view('emails.password-reset', ['resetLink' => $resetLink, 'user' => $user])->render();

        if ($mailer->sendEmail($request->email, 'Recuperación de contraseña - ' . config('app.name'), $view)) {
            return back()->with('status', 'Te hemos enviado el enlace de recuperación por correo electrónico.');
        } else {
            return back()->withErrors(['email' => 'No se pudo enviar el correo de recuperación. Inténtalo más tarde.']);
        }
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.custom-reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:usuarios,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $resetRecord = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'El token de recuperación es inválido o ha expirado.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect('/login')->with('status', 'Tu contraseña ha sido restablecida. Ya puedes iniciar sesión.');
    }
}

