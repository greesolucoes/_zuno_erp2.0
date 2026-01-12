<?php

namespace App\Http\Controllers\Petshop\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Hotel;
use App\Models\Petshop\Quarto;
use App\Models\Petshop\QuartoEvento;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoramentoHotelController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;
        $mes = $request->input('mes', now()->format('Y-m'));

        $inicio = Carbon::parse($mes . '-01')->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();
        $dias = CarbonPeriod::create($inicio, $fim);

        $reservas = Hotel::where('empresa_id', $empresa_id)
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('checkin', [$inicio, $fim])
                    ->orWhereBetween('checkout', [$inicio, $fim])
                    ->orWhere(function ($sub) use ($inicio, $fim) {
                        $sub->where('checkin', '<=', $inicio)
                            ->where('checkout', '>=', $fim);
                    });
            })->get();

        $ocupacoes = [];
        foreach ($reservas as $reserva) {
            $periodo = CarbonPeriod::create(
                $reserva->checkin->copy()->startOfDay(),
                $reserva->checkout->copy()->startOfDay()
            );
            foreach ($periodo as $data) {
                if ($data->between($inicio, $fim)) {
                    $ocupacoes[$reserva->quarto_id][$data->format('Y-m-d')] = $reserva;
                }
            }
        }

        $quartos = Quarto::where('empresa_id', $empresa_id)->orderBy('nome')->get();

        $eventos = QuartoEvento::whereIn('quarto_id', $quartos->pluck('id'))
            ->whereDate('inicio', '<=', $fim)
            ->where(function ($q) use ($inicio) {
                $q->whereNull('fim')
                    ->orWhereDate('fim', '>=', $inicio);
            })
            ->get();

        $manutencoes = [];
        foreach ($eventos as $evento) {
            $periodo = CarbonPeriod::create(
                $evento->inicio->copy()->startOfDay(),
                ($evento->fim ?? $evento->inicio)->copy()->startOfDay()
            );
            foreach ($periodo as $data) {
                if ($data->between($inicio, $fim)) {
                    $manutencoes[$evento->quarto_id][$data->format('Y-m-d')] = true;
                }
            }
        }

        return view('petshop.hotel.monitoramento_hotel', compact('quartos', 'dias', 'ocupacoes', 'mes', 'manutencoes'));
    }

    public function show(Quarto $quarto, Request $request)
    {
        $data = Carbon::parse($request->input('data'));

        $reservas = Hotel::with(['animal', 'cliente', 'servicos', 'produtos'])
            ->where('quarto_id', $quarto->id)
            ->whereDate('checkin', '<=', $data)
            ->whereDate('checkout', '>=', $data)
            ->get();

        $eventos = $quarto->eventos()
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
            'reservas' => $reservas->map(function ($hotel) {
                return [
                    'id' => $hotel->id,
                    'tutor' => $hotel->cliente?->nome_fantasia,
                    'pet' => $hotel->animal?->nome,
                    'checkin' => optional($hotel->checkin)->format('d/m H:i'),
                    'checkout' => optional($hotel->checkout)->format('d/m H:i'),
                    'status' => $hotel->estado,
                    'servicos_extras' => $hotel->servicos->pluck('nome')->implode(', '),
                    'produtos' => $hotel->produtos->pluck('nome')->implode(', '),
                    'observacoes' => $hotel->descricao,
                    'link' => route('hoteis.edit', $hotel->id),
                ];
            }),
            'faturamento' => $reservas->map(function ($hotel) {
                return [
                    'tutor' => $hotel->cliente?->nome_fantasia,
                    'pet' => $hotel->animal?->nome,
                    'valor' => $hotel->valor ?? 0,
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