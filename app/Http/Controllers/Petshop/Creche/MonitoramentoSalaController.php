<?php

namespace App\Http\Controllers\Petshop\Creche;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Creche;
use App\Models\Petshop\Turma;
use Illuminate\Support\Facades\Auth;

class MonitoramentoSalaController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $hoje = now()->toDateString();

        $turmas = Turma::where('empresa_id', $empresaId)->get();

        $ocupacoes = Creche::with(['animal', 'cliente'])
            ->where('empresa_id', $empresaId)
            ->whereDate('data_entrada', $hoje)
            ->get()
            ->groupBy('turma_id');

        $turmas->transform(function ($turma) use ($ocupacoes) {
            $ocupacoesTurma = $ocupacoes->get($turma->id) ?? collect();
            $turma->ocupados = $ocupacoesTurma->count();

            $turma->status_atual = $turma->status;
            if ($turma->status === Turma::STATUS_DISPONIVEL) {
                $turma->status_atual = $turma->ocupados > 0
                    ? Turma::STATUS_OCUPADO
                    : Turma::STATUS_DISPONIVEL;
            }

            $turma->reservas = $ocupacoesTurma;
            return $turma;
        });

        return view('petshop.creche.monitoramento_salas', compact('turmas'));
    }
}
