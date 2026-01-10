<?php

namespace App\Http\Controllers\PetShop\Planos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlanoAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('petshop.planos.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::guard('plano')->attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::guard('plano')->user();
            $now = Carbon::now();

            if (($user->data_inicial && $now->lt(Carbon::parse($user->data_inicial))) ||
                ($user->data_final && $now->gt(Carbon::parse($user->data_final)))) {
                Auth::guard('plano')->logout();
                return back()->with('error', 'Seu plano está fora da validade.')->withInput($request->only('email'));
            }

            return redirect()->route('petshop.planos.agendamentos.novo');
        }

        if (Auth::guard('portal')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('petshop.planos.agendamentos.novo');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::guard('plano')->check()) {
            Auth::guard('plano')->logout();
        }
        if (Auth::guard('portal')->check()) {
            Auth::guard('portal')->logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('petshop.planos.login');
    }
}