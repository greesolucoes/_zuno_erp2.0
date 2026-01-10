<?php

namespace App\Http\Controllers\Petshop\Public;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Estetica;
use Illuminate\Http\Request;

class HistoricoController extends Controller
{
    /**
     * Display the scheduling history for the authenticated plan user.
     */
    public function index(Request $request)
    {
        $user = auth('plano')->user();

        $agendamentos = collect();

        if ($user) {
            $agendamentos = Estetica::with(['servicos.servico', 'colaborador', 'ordemServico'])
                ->where('cliente_id', $user->cliente_id)
                ->orderByDesc('data_agendamento')
                ->orderByDesc('horario_agendamento')
                ->get();
        }

        return view('public.petshop.historico.index', [
            'agendamentos' => $agendamentos,
        ]);
    }
}