<?php

namespace App\Services\Petshop;

use App\Models\Petshop\{Plano, PlanoVersao, PlanoServico, PlanoProduto};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PlanoService
{
    /**
     * Paginate planos optionally filtering by search term.
     */
    public function paginate(?string $search): LengthAwarePaginator
    {
        $query = Plano::where('empresa_id', request()->empresa_id)
                      ->where('local_id', optional(__getLocalAtivo())->id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->paginate(env('PAGINACAO'));
    }

    /**
     * Create a new plano with optional nested relations.
     */
    public function create(array $data): Plano
    {
        return DB::transaction(function () use ($data) {
            $plano = Plano::create($this->filterPlanoFields($data));

            $this->handleVersoes($plano, $data['versoes'] ?? []);

            return $plano->load('versoes.servicos', 'versoes.produtos');
        });
    }

    /**
     * Update an existing plano.
     */
    public function update(Plano $plano, array $data): Plano
    {
        return DB::transaction(function () use ($plano, $data) {
            $plano->update($this->filterPlanoFields($data));

            // Replace existing versions to keep data in sync
            $plano->versoes()->delete();
            $this->handleVersoes($plano, $data['versoes'] ?? []);

            return $plano->load('versoes.servicos', 'versoes.produtos');
        });
    }

    /**
     * Remove a plano from storage.
     */
    public function delete(Plano $plano): void
    {
        DB::transaction(fn () => $plano->delete());
    }

    /**
     * Keep only fields that belong to the plano table.
     */
    private function filterPlanoFields(array $data): array
    {
        return Arr::only($data, [
            'slug',
            'nome',
            'descricao',
            'ativo',
            'empresa_id',
            'local_id',
            'periodo',
            'frequencia_tipo',
            'frequencia_qtd',
            'preco_plano',
            'multa_noshow_tipo',
            'multa_noshow_valor',
            'bloquear_por_inadimplencia',
            'dias_tolerancia_atraso',
        ]);
    }

    /**
     * Persist plano versions along with services and products.
     */
    private function handleVersoes(Plano $plano, array $versoes): void
    {
        foreach ($versoes as $versaoData) {
            $versao = $plano->versoes()->create([
                'vigente_desde' => $versaoData['vigente_desde'],
                'vigente_ate' => $versaoData['vigente_ate'] ?? null,
            ]);

            foreach ($versaoData['servicos'] ?? [] as $servico) {
                $versao->servicos()->create([
                    'servico_id' => $servico['servico_id'],
                    'qtd_por_ciclo' => $servico['qtd_por_ciclo'] ?? 1,
                    'valor_servico' => $servico['valor_servico'] ?? 0,
                    'coparticipacao_tipo' => $servico['coparticipacao_tipo'] ?? null,
                    'coparticipacao_valor' => $servico['coparticipacao_valor'] ?? null,
                ]);
            }

            foreach ($versaoData['produtos'] ?? [] as $produto) {
                $versao->produtos()->create([
                    'produto_id' => $produto['produto_id'],
                    'variacao_id' => $produto['variacao_id'] ?? null,
                    'qtd_por_ciclo' => $produto['qtd_por_ciclo'] ?? 1,
                ]);
            }
        }
    }
}