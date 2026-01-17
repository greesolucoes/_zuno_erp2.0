<?php

namespace App\Services\Petshop;

use App\Models\ContaReceber;
use App\Models\Petshop\Hotel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelService
{
    /**
     * Atualiza o id da conta a receber que está vinculada ao hotel
     * 
     * @param int $hotel_id id do hotel
     * @param int $conta_receber_id id da conta a receber
     */
    public function updateContaReceberId(int $hotel_id, int $conta_receber_id) {
        DB::transaction(function () use ($hotel_id, $conta_receber_id) {
            $hotel = Hotel::findOrFail($hotel_id);
            $conta_receber = ContaReceber::findOrFail($conta_receber_id);

            if ((int)$conta_receber->empresa_id !== (int)$hotel->empresa_id) {
                throw new \Exception('Conta a receber não pertence à mesma empresa do agendamento.');
            }

            $conta_receber->hotel_id = $hotel_id;
            $conta_receber->save();
        });
    }

    /**
     * Atualiza o valor total do hotel e da conta a receber (caso exista e não esteja paga)
     * com base no valor do serviço de reserva,
     * serviços extras, produtos e frete
     * 
     * @param int $hotel_id id do hotel
     */
    public function updateValorTotal(int $hotel_id) {
        $hotel = Hotel::findOrFail($hotel_id);

        $total_servicos = 0;
        $hotel->servicos->each(function ($servico) use (&$total_servicos) {
            $total_servicos += $servico->pivot->valor_servico;
        });

        $total_produtos = 0;
        $hotel->produtos->each(function ($produto) use (&$total_produtos) {
            $total_produtos += $produto->valor_unitario * $produto->pivot->quantidade;
        });

        $valor_total = $total_servicos + $total_produtos;

        $hotel->valor = $valor_total;
        $hotel->save();

        if (isset($hotel->contaReceber) && $hotel->contaReceber->status == 0) {
            $hotel->contaReceber->valor_integral = $valor_total;

            $hotel->contaReceber->save();
        }

        if (isset($hotel->ordemServico)) {
            $hotel->ordemServico->total_sem_desconto = $valor_total;
            $hotel->ordemServico->valor = $valor_total;
            
            $hotel->ordemServico->save();
        }
    }

    /**
     * Atualiza a data de vencimento da conta a receber
     * com base no check out da reserva
     * 
     * @param int $hotel_id id do hotel
     */
    public function updateContaReceberDataVencimento(int $hotel_id) 
    {
        $hotel = Hotel::findOrFail($hotel_id);

        if ($hotel->contaReceber && $hotel->contaReceber->status == 0) {
            $hotel->contaReceber->data_vencimento = $hotel->checkout->format('Y-m-d');
            $hotel->contaReceber->save();
        }
    }

    /**
     * Remove a conta a receber vinculada ao hotel
     * 
     * @param int $hotel_id id do hotel
    */
    public function removeContaReceber(int $hotel_id)
    {
        $hotel = Hotel::findOrFail($hotel_id);

        if ($hotel->contaReceber) {
            $hotel->contaReceber->delete();
        }
    }

    /**
     * Atualiza o serviço de frete do hotel 
     * já atualizando o valor total de todos os serviços e produtos vinculados
     * 
     * @param int $hotel_id id do hotel
     * @param array $servico_data dados do serviço de frete
     */
    public function updateServicoFrete(int $hotel_id, array $servico_data)
    {
        $hotel = Hotel::findOrFail($hotel_id);

        $old_servico_frete = $hotel->servicos->filter(function ($servico) {
            return $servico->categoria->nome == 'FRETE';
        })->first();

        if (isset($old_servico_frete)) {
            $hotel->servicos()->detach($old_servico_frete->id);
        }

        if ($servico_data['servico_id']) {
            $hotel->servicos()->attach($servico_data['servico_id'], [
                'data_servico' => Carbon::now()->format('Y-m-d'),
                'hora_servico' => Carbon::now()->format('H:i:s'),
                'valor_servico' => $servico_data['valor_servico'],
            ]);
        }

        $this->updateValorTotal($hotel_id);
    }

    /**
     * Cria ou atualiza o endereço do frete selecionado pelo cliente no agendamento de hotel
     * 
     * @param int $hotel_id id do hotel
     * @param array $data dados do endereço
     * 
     * @return void
     */
    public function updateOrCreateHotelClienteEndereco(int $hotel_id, array $data) 
    {
        $hotel = Hotel::findOrFail($hotel_id);

        $hotel->hotelClienteEndereco()->updateOrCreate(
            [
                'hotel_id'   => $hotel->id,
                'cliente_id' => $hotel->cliente_id,
            ],
            $data
        );
    }
}
