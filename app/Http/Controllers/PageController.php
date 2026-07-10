<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function index()      { return view('index'); }
    public function dashboard()  { return view('dashboard'); }
    public function crear()      { return view('crear'); }
    public function propuesta()  { return view('propuesta'); }
    public function faq()        { return view('faq'); }
    public function contacto()   { return view('contacto'); }
    public function comunidad()  { return view('comunidad'); }
    public function privacidad() { return view('privacidad'); }
    public function terminos()   { return view('terminos'); }

    public function perfil()
    {
        if (!Auth::check()) return redirect('/auth.php');
        return view('perfil');
    }

    public function admin()
    {
        if (!Auth::check() || !Auth::user()->esAdmin()) {
            return redirect('/dashboard.php');
        }
        return view('admin.admin');
    }

    public function auth()
    {
        if (Auth::check()) return redirect('/dashboard.php');
        return view('auth.auth');
    }
}
