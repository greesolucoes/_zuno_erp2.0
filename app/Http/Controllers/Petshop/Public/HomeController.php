<?php

namespace App\Http\Controllers\Petshop\Public;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Estetica;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the public page for pet shop plans.
     */
    public function index(Request $request, $empresa = null)
    {
        $user = auth('plano')->user();

        $plano = optional($user)->plano;

        $agendamentos = collect();
        if ($user) {
            $nowDate = now()->toDateString();
            $nowTime = now()->format('H:i');

            $agendamentos = Estetica::with(['servicos.servico', 'colaborador'])
                ->where('cliente_id', $user->cliente_id)
                ->whereNotIn('estado', ['rejeitado', 'cancelado'])
                ->where(function ($q) use ($nowDate, $nowTime) {
                    $q->whereDate('data_agendamento', '>', $nowDate)
                      ->orWhere(function ($q) use ($nowDate, $nowTime) {
                          $q->whereDate('data_agendamento', $nowDate)
                            ->where('horario_agendamento', '>=', $nowTime);
                      });
                })
                ->orderBy('data_agendamento')
                ->orderBy('horario_agendamento')
                ->limit(5)
                ->get();
        }

        return view('public.petshop.index', [
            'empresa' => $empresa,
            'plano' => $plano,
            'agendamentos' => $agendamentos,
        ]);
    }
}