<?php

namespace App\Http\Controllers\Petshop\Creche;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Creche;
use App\Models\Petshop\Turma;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoramentoCrecheController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $mes = $request->input('mes', now()->format('Y-m'));

        $inicio = Carbon::parse($mes . '-01')->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();
        $dias = CarbonPeriod::create($inicio, $fim);

        $reservas = Creche::where('empresa_id', $empresaId)
            ->where(
                'data_entrada', '>=', $inicio->format('Y-m-d'),
            )
            ->where(
                'data_saida', '<=', $fim->format('Y-m-d'),
            )
        ->get();

        $ocupacoes = [];
        foreach ($reservas as $reserva) {
            $periodo = CarbonPeriod::create(
                Carbon::parse($reserva->data_entrada)->copy()->startOfDay(),
                Carbon::parse($reserva->data_saida)->copy()->startOfDay(),
            );

            foreach ($periodo as $data) {
                if ($data->between($inicio, $fim)) {
                    $ocupacoes[$reserva->turma_id][$data->format('Y-m-d')] = $reserva;
                }
            }
        }

        $turmas = Turma::where('empresa_id', $empresaId)->orderBy('nome')->get();

        return view('petshop.creche.monitoramento_creche', compact('turmas', 'dias', 'ocupacoes', 'mes'));
    }

    public function show(Turma $turma, Request $request)
    {
        $data = Carbon::parse($request->input('data'));

        $reservas = Creche::with(['animal', 'cliente', 'servicos'])
            ->where('turma_id', $turma->id)
            ->whereDate('data_entrada', '<=', $data)
            ->whereDate('data_saida', '>=', $data)
            ->get();

        $eventos = $turma->eventos()
            ->with(['servico', 'prestador'])
            ->whereDate('inicio', '<=', $data)
            ->where(function ($q) use ($data) {
                $q->whereNull('fim')
                    ->orWhereDate('fim', '>=', $data);
            })
            ->get();

        $status = 'Sem reserva';
        if ($eventos->isNotEmpty()) {
            $status = 'Com evento';
        } elseif ($reservas->isNotEmpty()) {
            $status = 'Com reserva';
        }

        return response()->json([
            'data' => $data->toDateString(),
            'status' => $status,
            'reservas' => $reservas->map(function ($creche) {
                return [
                    'id' => $creche->id,
                    'tutor' => $creche->cliente?->nome_fantasia,
                    'pet' => $creche->animal?->nome,
                    'status' => Creche::getStatusCreche($creche->estado),
                    'servicos_extras' => $creche->servicos->pluck('nome')->implode(', '),
                    'observacoes' => $creche->descricao,
                    'link' => route('creches.edit', $creche->id),
                ];
            }),
            'faturamento' => $reservas->map(function ($creche) {
                return [
                    'tutor' => $creche->cliente?->nome_fantasia,
                    'pet' => $creche->animal?->nome,
                    'valor' => $creche->valor ?? 0,
                ];
            }),
            'total_faturamento' => $reservas->sum('valor'),
            'despesas' => $eventos->map(function ($evento) {
                return [
                    'inicio' => optional($evento->inicio)->format('d/m H:i'),
                    'fim' => optional($evento->fim)->format('d/m H:i'),
                    'servico' => $evento->servico?->nome,
                    'prestador' => $evento->prestador?->nome,
                    'valor' => $evento->servico?->valor ?? 0,
                ];
            }),
            'total_despesas' => $eventos->sum(function ($evento) {
                return $evento->servico->valor ?? 0;
            }),
        ]);
    }
}