<?php

namespace App\Http\Controllers\Petshop\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Quarto;
use App\Models\Petshop\Hotel;
use Illuminate\Support\Facades\Auth;
use App\Models\Petshop\QuartoEvento;

class MonitoramentoQuartoController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $hoje = now()->toDateString();

        $quartos = Quarto::where('empresa_id', $empresaId)->get();

        $ocupacoes = Hotel::with(['animal', 'cliente'])
            ->where('empresa_id', $empresaId)
            ->whereDate('checkin', '<=', $hoje)
            ->whereDate('checkout', '>=', $hoje)
            ->get()
            ->groupBy('quarto_id');

        $eventos = QuartoEvento::with(['servico', 'prestador'])
            ->whereDate('inicio', $hoje)
            ->whereHas('quarto', function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->get()
            ->groupBy('quarto_id');

        $quartos->transform(function ($quarto) use ($ocupacoes, $eventos) {
            $ocupacoesQuarto = $ocupacoes->get($quarto->id) ?? collect();
            $quarto->ocupados = $ocupacoesQuarto->count();

            $quarto->status_atual = $quarto->status;

            if ($quarto->status === Quarto::STATUS_DISPONIVEL) {
                $quarto->status_atual = $quarto->ocupados > 0
                    ? Quarto::STATUS_EM_USO
                    : Quarto::STATUS_DISPONIVEL;
            }

            $quarto->reserva = $ocupacoesQuarto->first();
            $quarto->eventos = $eventos->get($quarto->id) ?? collect();
            return $quarto;
        });

        return view('petshop.hotel.monitoramento_quartos', compact('quartos'));
    }
}