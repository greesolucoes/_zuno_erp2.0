<?php

namespace App\Http\Controllers\Petshop\Public;

use App\Http\Controllers\Controller;
use App\Models\ContaReceber;

class PlanoController extends Controller
{
    /**
     * Show the plan details view for plan users.
     */
    public function index()
    {
        $user = auth('plano')->user();

        $plano = optional($user)->plano;

        $conta = null;
        if ($user && $plano) {
            $conta = ContaReceber::with('formaPagamento')
                ->where('cliente_id', $user->cliente_id)
                ->where('plano_id', $plano->id)
                ->orderByDesc('id')
                ->first();
        }

        return view('public.petshop.plano.index', [
            'plano' => $plano,
            'conta' => $conta,
        ]);
    }
}