<?php

namespace App\Services\Petshop;

use App\Models\PlanoUser;
use App\Models\OrdemServico;
use App\Models\ServicoOs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlanoLimiteService
{
    /**
     * Verifica se o usuário do plano pode utilizar um serviço específico.
     */
    public function podeUsarServico(PlanoUser $usuario, int $servicoId): bool
    {
        $plano = $usuario->plano;
        Log::info('[PlanoLimite] Iniciando verificação', [
            'plano_user_id' => $usuario->id,
            'servico_id'    => $servicoId,
            'plano_id'      => optional($plano)->id,
        ]);

        if (!$plano || $plano->frequencia_tipo !== 'limitado') {
            Log::info('[PlanoLimite] Plano sem limite ou inexistente');
            return true;
        }

        $agora = Carbon::now();
        $inicioPlano = Carbon::parse($usuario->data_inicial);
        $fimPlano    = $usuario->data_final ? Carbon::parse($usuario->data_final) : null;

        if ($agora->lt($inicioPlano) || ($fimPlano && $agora->gt($fimPlano))) {
            Log::info('[PlanoLimite] Plano fora da vigência', [
                'data_inicial' => $usuario->data_inicial,
                'data_final'   => $usuario->data_final,
            ]);
            return false;
        }

        [$inicioCiclo, $fimCiclo] = $this->intervaloAtual($inicioPlano, $plano->periodo, $agora);
        Log::info('[PlanoLimite] Intervalo do ciclo', [
            'inicio' => $inicioCiclo->toDateString(),
            'fim'    => $fimCiclo->toDateString(),
        ]);

        $usos = ServicoOs::where('servico_id', $servicoId)
            ->whereHas('ordemServico', function ($q) use ($usuario, $inicioCiclo, $fimCiclo) {
                $q->where('cliente_id', $usuario->cliente_id)
                    ->whereDate('data_inicio', '>=', $inicioCiclo->toDateString())
                    ->whereDate('data_inicio', '<', $fimCiclo->toDateString())
                    ->whereNotIn('estado', [
                        OrdemServico::STATUS_CANCELADO,
                        OrdemServico::STATUS_REJEITADO,
                    ]);
            })
            ->count();

        Log::info('[PlanoLimite] Uso atual do serviço', [
            'usos'   => $usos,
            'limite' => (int) $plano->frequencia_qtd,
        ]);

        return $usos < (int) $plano->frequencia_qtd;
    }

    private function intervaloAtual(Carbon $inicioPlano, string $periodo, Carbon $agora): array
    {
        $inicio = $inicioPlano->copy();
        while ($this->adicionaPeriodo($inicio, $periodo)->lte($agora)) {
            $inicio = $this->adicionaPeriodo($inicio, $periodo);
        }
        $fim = $this->adicionaPeriodo($inicio, $periodo);
        return [$inicio, $fim];
    }

    private function adicionaPeriodo(Carbon $data, string $periodo): Carbon
    {
        return match ($periodo) {
            'dia'    => $data->copy()->addDay(),
            'semana' => $data->copy()->addWeek(),
            'ano'    => $data->copy()->addYear(),
            default  => $data->copy()->addMonth(),
        };
    }
}