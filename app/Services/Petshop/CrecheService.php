<?php

namespace App\Services\Petshop;

use App\Models\ContaReceber;
use App\Models\Petshop\Creche;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CrecheService
{
    /**
     * Atualiza o id da conta a receber que está vinculada a creche
     * 
     * @param int $creche_id id da creche
     * @param int $conta_receber_id id da conta a receber
     */
    public function updateContaReceberId(int $creche_id, int $conta_receber_id) {
        DB::transaction(function () use ($creche_id, $conta_receber_id) {
            $creche = Creche::findOrFail($creche_id);
            $conta_receber = ContaReceber::findOrFail($conta_receber_id);

            if ((int)$conta_receber->empresa_id !== (int)$creche->empresa_id) {
                throw new \Exception('Conta a receber não pertence à mesma empresa do agendamento.');
            }

            $conta_receber->creche_id = $creche_id;
            $conta_receber->save();
        });
    }

    /**
     * Atualiza o valor total da creche e da conta a receber (caso exista e não esteja paga)
     * com base no valor do serviço de reserva,
     * serviços extras, produtos e frete
     * 
     * @param int $creche_id id do creche
     */
    public function updateValorTotal(int $creche_id) {
        $creche = Creche::findOrFail($creche_id);

        $total_servicos = 0;
        $creche->servicos->each(function ($servico) use (&$total_servicos) {
            $total_servicos += $servico->pivot->valor_servico;
        });

        $total_produtos = 0;
        $creche->produtos->each(function ($produto) use (&$total_produtos) {
            $total_produtos += $produto->valor_unitario * $produto->pivot->quantidade;
        });

        $valor_total = $total_servicos + $total_produtos;

        $creche->valor = $valor_total;
        $creche->save();

        if (isset($creche->contaReceber) && $creche->contaReceber->status == 0) {
            $creche->contaReceber->valor_integral = $valor_total;

            $creche->contaReceber->save();
        }
    }

    /**
     * Atualiza a data de vencimento da conta a receber
     * com base na data de saída da reserva 
     * 
     * @param int $creche_id id da creche
     */
    public function updateContaReceberDataVencimento(int $creche_id) 
    {
        $creche = Creche::findOrFail($creche_id);

        if ($creche->contaReceber && $creche->contaReceber->status == 0) {
            $creche->contaReceber->data_vencimento = Carbon::parse($creche->data_saida)->format('Y-m-d');
            $creche->contaReceber->save();
        }
    }

    /**
     * Remove a conta a receber vinculada a creche
     * 
     * @param int $creche_id id da creche
    */
    public function removeContaReceber(int $creche_id)
    {
        $creche = Creche::findOrFail($creche_id);

        if ($creche->contaReceber) {
            $creche->contaReceber()->delete();
        }
    }

    /**
     * Atualiza o serviço de frete da creche 
     * já atualizando o valor total de todos os serviços e produtos vinculados
     * 
     * @param int $creche_id id da creche
     * @param array $servico_data dados do serviço de frete
     */
    public function updateServicoFrete(int $creche_id, array $servico_data)
    {
        $creche = Creche::findOrFail($creche_id);

        $old_servico_frete = $creche->servicos->filter(function ($servico) {
            return $servico->categoria->nome == 'FRETE';
        })->first();

        if (isset($old_servico_frete)) {
            $creche->servicos()->detach($old_servico_frete->id);
        }

        if ($servico_data['servico_id']) {
            $creche->servicos()->attach($servico_data['servico_id'], [
                'data_servico' => Carbon::now()->format('Y-m-d'),
                'hora_servico' => Carbon::now()->format('H:i:s'),
                'valor_servico' => $servico_data['valor_servico'],
            ]);
        }

        $this->updateValorTotal($creche_id);
    }

    /**
     * Cria ou atualiza o endereço do frete selecionado pelo cliente no agendamento de creche
     * 
     * @param int $creche_id id da creche
     * @param array $data dados do endereço
     * 
     * @return void
    */
    public function updateOrCreateCrecheClienteEndereco(int $creche_id, array $data) 
    {
        $creche = Creche::findOrFail($creche_id);

        $creche->crecheClienteEndereco()->updateOrCreate(
            [
                'creche_id'   => $creche->id,
                'cliente_id' => $creche->cliente_id,
            ],
            $data
        );
    }
}
