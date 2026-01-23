<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Processa o login
     */
    public function login(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string',
            'password' => 'required|string',
        ], [
            'cpf.required' => 'O CPF é obrigatório',
            'password.required' => 'A senha é obrigatória',
        ]);

        // Remove qualquer formatação do CPF
        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);

        // Valida se o CPF tem 11 dígitos após limpar
        if (strlen($cpf) !== 11) {
            return back()->withErrors([
                'cpf' => 'O CPF deve conter exatamente 11 dígitos.',
            ])->onlyInput('cpf');
        }

        // Busca o usuário pelo CPF
        $user = User::where('cpf', $cpf)->first();

        if (!$user || !$user->is_active) {
            return back()->withErrors([
                'cpf' => 'CPF ou senha incorretos.',
            ])->onlyInput('cpf');
        }

        // Verifica a senha
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'cpf' => 'CPF ou senha incorretos.',
            ])->onlyInput('cpf');
        }

        // Faz o login
        Auth::login($user, $request->filled('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Faz logout do usuário
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
