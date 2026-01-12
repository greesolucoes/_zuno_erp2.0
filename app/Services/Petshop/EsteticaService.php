<?php

namespace App\Services\Petshop;

use App\Models\ContaReceber;
use App\Models\OrdemServico;
use App\Models\Petshop\Estetica;
use App\Models\Petshop\EsteticaServico;
use App\Models\PlanoUser;
use App\Models\ProdutoOs;
use App\Models\ServicoOs;
use App\Services\Notificacao\EsteticaNotificacaoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EsteticaService
{
    public function __construct(private PlanoLimiteService $limiteService) {}

    /**
     * Atualiza o id da conta a receber que está vinculada a estética
     * 
     * @param int $estetica_id id da estética
     * @param int $conta_receber_id id da conta a receber
     */
    public function updateContaReceberId(int $estetica_id, int $conta_receber_id) {
        $conta_receber = ContaReceber::findOrFail($conta_receber_id);

        $conta_receber->estetica_id = $estetica_id;

        $estetica = Estetica::findOrFail($estetica_id);
        
        $estetica->save();
        $conta_receber->save();
    }

    /**
     * Atualiza o valor total da conta a receber da estética (caso exista e não esteja paga)
     * com base no valor dos serviços da reserva, produtos e frete
     * 
     * @param int $estetica_id id do estetica
     */
    public function updateValorTotal(int $estetica_id) {
        $estetica = Estetica::findOrFail($estetica_id);

        $total_servicos = 0;
        $estetica->servicos->each(function ($servico) use (&$total_servicos) {
            $total_servicos += $servico->subtotal;
        });

        $total_produtos = 0;
        $estetica->produtos->each(function ($produto) use (&$total_produtos) {
            $total_produtos += $produto->valor * $produto->quantidade;
        });

        $valor_total = $total_servicos + $total_produtos;

        if (isset($estetica->contaReceber) && $estetica->contaReceber->status == 0) {
            $estetica->contaReceber->valor_integral = $valor_total;

            $estetica->contaReceber->save();
        }
    }

    /**
     * Atualiza a data de vencimento da conta a receber da estética
     * com base na data de agendamento
     * 
     * @param int $estetica_id id da estética
     */
    public function updateContaReceberDataVencimento(int $estetica_id) 
    {
        $estetica = Estetica::findOrFail($estetica_id);

        if ($estetica->contaReceber && $estetica->contaReceber->status == 0) {
            $estetica->contaReceber->data_vencimento = $estetica->data_agendamento->format('Y-m-d');
            $estetica->contaReceber->save();
        }
    }
    
    /**
     * Aprova um agendamento de estética e cria uma ordem de serviço 
     * a partir dele caso não haja conflito
     * 
     * @param Estetica $estetica estética a ser aprovada 
     */
    public function aprovar(Estetica $estetica)
    {
        $inicio = Carbon::parse($estetica->data_agendamento)->setTimeFromTimeString($estetica->horario_agendamento);
        $duracao = $estetica->servicos->sum(fn($s) => $s->servico ? $s->servico->tempo_execucao : 0);
        $fim = $inicio->copy()->addMinutes($duracao);

        $conflito = Estetica::with('servicos.servico')
            ->where('colaborador_id', $estetica->colaborador_id)
            ->whereDate('data_agendamento', $estetica->data_agendamento)
            ->where('estado', '!=', 'rejeitado')
            ->where('id', '!=', $estetica->id)
            ->get()
            ->first(function ($a) use ($inicio, $fim) {
                $a_inicio = Carbon::parse($a->data_agendamento)->setTimeFromTimeString($a->horario_agendamento);
                $a_duracao = $a->servicos->sum(fn($s) => $s->servico ? $s->servico->tempo_execucao : 0);
                $a_fim = $a_inicio->copy()->addMinutes($a_duracao);
                return $inicio->lt($a_fim) && $fim->gt($a_inicio);
            });

        if ($conflito) {
            return [
                'success' => false,
                'message' => 'Profissional com horário conflitante.'
            ];
        }

        if (!$estetica->ordem_servico_id) {
            if (!$this->criarOrdemServico($estetica)) {
                return [
                    'success' => false,
                    'message' => 'Limite de uso do serviço atingido para este período.'
                ];
            }
        }

        $estetica->estado = 'agendado';
        $estetica->save();

        $esteticaParaNotificacao = $estetica->fresh(['empresa', 'cliente', 'animal', 'servicos.servico']);
        (new EsteticaNotificacaoService())->statusAtualizado($esteticaParaNotificacao ?? $estetica);

        return [
            'success' => true,
            'message' => 'Agendamento aprovado com sucesso!'
        ];
    }

    public function rejeitar(Estetica $estetica)
    {
        $estetica->estado = 'rejeitado';
        $estetica->save();

        if ($estetica->ordemServico) {
            $estetica->ordemServico->estado = OrdemServico::STATUS_REJEITADO;
            $estetica->ordemServico->save();
        }

        $esteticaParaNotificacao = $estetica->fresh(['empresa', 'cliente', 'animal', 'servicos.servico']);
        (new EsteticaNotificacaoService())->statusAtualizado($esteticaParaNotificacao ?? $estetica);

        session()->flash('flash_success', 'Agendamento rejeitado com sucesso!');

        return [
            'success' => true,
            'message' => 'Agendamento rejeitado com sucesso!'
        ];
    }

    public function criarOrdemServico(Estetica $estetica): bool
    {
        $estetica->load(['servicos.servico', 'produtos']);

        if ($estetica->plano_id) {
            $planoUser = PlanoUser::where('cliente_id', $estetica->cliente_id)
                ->where('plano_id', $estetica->plano_id)
                ->orderByDesc('data_inicial')
                ->first();

            if ($planoUser) {
                foreach ($estetica->servicos as $servico) {
                    if (!$this->limiteService->podeUsarServico($planoUser, $servico->servico_id)) {
                        Log::info('[PlanoLimite] Limite atingido ao criar OS', [
                            'plano_user_id' => $planoUser->id,
                            'servico_id'    => $servico->servico_id,
                        ]);
                        return false;
                    }
                }
            }
        }

        $valorServicos = $estetica->servicos->sum(fn($s) => $s->subtotal);
        $tempo_execucao_servicos = $estetica->servicos->sum(fn($s) => $s->servico->tempo_execucao);
        $valorProdutos = $estetica->produtos->sum('subtotal');
        $valorTotal = $valorServicos + $valorProdutos;

        $codigoSequencial = (OrdemServico::where('empresa_id', $estetica->empresa_id)
            ->max('codigo_sequencial') ?? 0) + 1;

        $data = Carbon::parse($estetica->data_agendamento)
            ->setTimeFromTimeString($estetica->horario_agendamento);

        $data_final_agendamento = $data->copy()->addMinutes($tempo_execucao_servicos);

        $ordem = OrdemServico::create([
            'descricao'          => 'Ordem de Serviço Estetica',
            'cliente_id'         => $estetica->cliente_id,
            'empresa_id'         => $estetica->empresa_id,
            'funcionario_id'     => $estetica->colaborador_id,
            'animal_id'          => $estetica->animal_id,
            'plano_id'           => null,
            'modulos'            => 'Estetica',
            'modulo_ids'         => ['Estetica' => [$estetica->id]],
            'usuario_id'         => auth()->id(),
            'codigo_sequencial'  => $codigoSequencial,
            'valor'              => $valorTotal,
            'total_sem_desconto' => $valorTotal,
            'data_inicio'        => $data,
            'data_entrega'       => $data_final_agendamento,
            'estado'             => OrdemServico::STATUS_AGENDADO,
        ]);

        $estetica->update(['ordem_servico_id' => $ordem->id]);

        foreach ($estetica->servicos as $servico) {
            ServicoOs::create([
                'ordem_servico_id' => $ordem->id,
                'servico_id'       => $servico->servico_id,
                'quantidade'       => 1,
                'valor'            => $servico->subtotal,
                'subtotal'         => $servico->subtotal,
            ]);
        }

        foreach ($estetica->produtos as $produto) {
            $valorOriginalProduto = ($produto->valor ?? 0) * ($produto->quantidade ?? 0);
            ProdutoOs::create([
                'ordem_servico_id' => $ordem->id,
                'produto_id'       => $produto->produto_id,
                'quantidade'       => $produto->quantidade,
                'valor'            => $produto->valor,
                'subtotal'         => $produto->subtotal,
                'desconto'         => $valorOriginalProduto - $produto->subtotal,
            ]);
        }

        return true;
    }

    /**
     * Remove a conta a receber vinculada a estética
     * 
     * @param int $estetica_id id da estética
    */
    public function removeContaReceber(int $estetica_id)
    {
        $estetica = Estetica::findOrFail($estetica_id);

        if ($estetica->contaReceber) {
            $estetica->contaReceber->delete();
        }
    }

    /**
     * Atualiza o serviço de frete da estética 
     * já atualizando o valor total de todos os serviços e produtos vinculados
     * 
     * @param int $estetica_id id da estética
     * @param array $servico_data dados do serviço de frete
     */
    public function updateServicoFrete(int $estetica_id, array $servico_data)
    {
        $estetica = Estetica::findOrFail($estetica_id);

        $old_servico_frete = $estetica->servicos->filter(function ($item) {
            return $item->servico->categoria->nome == 'FRETE';
        })->first();

        if (isset($old_servico_frete)) {
            $old_servico_frete->delete();
        }

        if ($servico_data['servico_id']) {
            EsteticaServico::create([
                'estetica_id' => $estetica->id,
                'servico_id' => $servico_data['servico_id'],
                'subtotal' => $servico_data['valor_servico'],
            ]);
        }

        $this->updateValorTotal($estetica_id);
    }

    /**
     * Cria ou atualiza o endereço do frete selecionado pelo cliente no agendamento de estética
     * 
     * @param int $estetica_id id da estética
     * @param array $data dados do endereço
     * 
     * @return void
    */
    public function updateOrCreateEsteticaClienteEndereco(int $estetica_id, array $data) 
    {
        $estetica = Estetica::findOrFail($estetica_id);

        $estetica->esteticaClienteEndereco()->updateOrCreate(
            [
                'estetica_id'   => $estetica->id,
                'cliente_id' => $estetica->cliente_id,
            ],
            $data
        );
    }
}