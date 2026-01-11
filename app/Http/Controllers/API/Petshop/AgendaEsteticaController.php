<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\Petshop\Configuracao;
use App\Models\Petshop\Estetica;
use App\Models\Servico;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Petshop\PlanoServico;

class AgendaEsteticaController extends Controller
{
    public function buscarHorarios(Request $request)
    {
        $this->_validate($request);

        try {
        $servicosParam  = $request->servicos;
        $servicos       = is_string($servicosParam) ? json_decode($servicosParam, true) : (array) $servicosParam;
        $data           = $request->data;
        $empresa_id     = $request->empresa_id;
        $funcionario_id = $request->funcionario_id;

        Log::info('Buscar horários estética', [
            'servicos'       => $servicos,
            'data'           => $data,
            'empresa_id'     => $empresa_id,
            'funcionario_id' => $funcionario_id,
        ]);

        $totalServico = 0;
        $tempoServico = 0;
        foreach ($servicos as $s) {
            $item = Servico::findOrFail($s);
            $tempoServico += $item->tempo_execucao;

            $plano = PlanoServico::where('servico_id', $s)->first();
            $valor = (float) $item->valor;
            if ($plano && $plano->coparticipacao_tipo && $plano->coparticipacao_valor !== null) {
                if ($plano->coparticipacao_tipo === 'percentual') {
                    $valor = $valor * ($plano->coparticipacao_valor / 100);
                } elseif ($plano->coparticipacao_tipo === 'valor_fixo') {
                    $valor = (float) $plano->coparticipacao_valor;
                }
            }
            $totalServico += $valor;
        }

        Log::debug('Tempo e valor dos serviços calculados', [
            'tempo_total' => $tempoServico,
            'valor_total' => $totalServico,
        ]);

        $horarios   = [];
        $funcionario = Funcionario::find($funcionario_id);
        $isToday     = Carbon::parse($data)->isToday();
        $agora       = now();

        // Busca configuração da filial; se não existir, tenta configuração geral da empresa
        $config = Configuracao::with('horarios')
            ->where('filial_id', $empresa_id)
            ->first();

        if (!$config) {
            $config = Configuracao::with('horarios')
                ->where('empresa_id', $empresa_id)
                ->whereNull('filial_id')
                ->first();
        }

        // Se ainda não encontrou configuração, tenta usar a empresa do colaborador
        if (!$config && $funcionario) {
            $config = Configuracao::with('horarios')
                ->where('filial_id', $funcionario->empresa_id)
                ->first();

            if (!$config) {
                $config = Configuracao::with('horarios')
                    ->where('empresa_id', $funcionario->empresa_id)
                    ->whereNull('filial_id')
                    ->first();
            }
        }
        $diaSemana = Carbon::parse($data)->dayOfWeek;

        // Jornada por funcionário é opcional e pode não estar implementada neste projeto.
        // Quando não existir, usamos apenas os horários configurados no Petshop (configuração global).

        $intervalos = $config?->horarios->where('dia_semana', $diaSemana);

        if ($intervalos && $intervalos->isNotEmpty()) {
            // A disponibilidade baseada na configuração do petshop é global.
            // Qualquer agendamento da empresa bloqueia o horário, independentemente do colaborador.

            $agendamentos = Estetica::with('servicos.servico')
                ->where('empresa_id', $config->empresa_id)
                ->whereDate('data_agendamento', $data)
                ->where('estado', '!=', 'rejeitado')
                ->get()
                ->map(function ($a) {
                    $inicio = Carbon::parse($a->data_agendamento)->setTimeFromTimeString($a->horario_agendamento);
                    $duracao = $a->servicos->sum(fn($s) => $s->servico ? $s->servico->tempo_execucao : 0);

                    return [
                        'inicio' => $inicio,
                        'fim'    => $inicio->copy()->addMinutes($duracao),
                    ];
                });

            foreach ($intervalos as $intervalo) {
                $inicioJornada = Carbon::parse("$data {$intervalo->hora_inicio}");
                $fimJornada    = Carbon::parse("$data {$intervalo->hora_fim}");

                $cursor = $inicioJornada->copy();
                while ($cursor->lt($fimJornada)) {
                    $slotInicio = $cursor->copy();
                    $slotFim    = $slotInicio->copy()->addMinutes($tempoServico);

                    if ($isToday && $slotInicio->lt($agora)) {
                        $cursor = $cursor->addMinutes($tempoServico);
                        continue;
                    }

                    $conflito = $agendamentos->first(function ($a) use ($slotInicio, $slotFim) {
                        return $slotInicio->lt($a['fim']) && $slotFim->gt($a['inicio']);
                    });

                    if (!$conflito && $slotFim->lte($fimJornada)) {
                        $horarios[] = [
                            'funcionario_id'   => $funcionario?->id,
                            'funcionario_nome' => $funcionario?->nome,
                            'inicio'           => $slotInicio->format('H:i'),
                            'fim'              => $slotFim->format('H:i'),
                            'data'             => $data,
                            'total'            => $totalServico,
                            'tempoServico'     => $tempoServico,
                        ];
                    }

                    $cursor = $cursor->addMinutes($tempoServico);
                }
            }

            return response()->json($horarios, 200);
        }

        return response()->json([], 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    private function _validate(Request $request)
    {
        $rules = [
            'empresa_id' => 'required',
            'funcionario_id' => 'required',
            'data' => 'required',
            'servicos' => 'required',
        ];
        $messages = [];
        $this->validate($request, $rules, $messages);
    }
}
