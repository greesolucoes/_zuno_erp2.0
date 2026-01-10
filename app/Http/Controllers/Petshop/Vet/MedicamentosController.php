<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petshop\SalvarMedicamentoRequest;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Medicamento;
use App\Models\Produto;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class MedicamentosController extends Controller
{
    public function index(Request $request): View|ViewFactory
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;

        $query = Medicamento::query()
            ->with(['produto.estoque', 'produto.estoqueLocais', 'especies'])
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nome_comercial', 'like', "%{$search}%")
                    ->orWhere('nome_generico', 'like', "%{$search}%")
                    ->orWhere('indicacoes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('classe_terapeutica')) {
            $query->where('classe_terapeutica', $request->string('classe_terapeutica'));
        }

        if ($request->filled('via_administracao')) {
            $query->where('via_administracao', $request->string('via_administracao'));
        }

        $medicamentos = $query
            ->orderBy('nome_comercial')
            ->get()
            ->map(fn(Medicamento $medicamento) => $this->formatarMedicamento($medicamento));

        $opcoesClasseTerapeutica = $this->buscarOpcoesFiltro('classe_terapeutica', 'Todas as categorias');
        $opcoesViaAdministracao = $this->buscarOpcoesFiltro('via_administracao', 'Todas as vias');

        return view('petshop.vet.medicines.index', [
            'medicines' => $medicamentos,
            'therapeuticClassOptions' => $opcoesClasseTerapeutica,
            'routeOptions' => $opcoesViaAdministracao,
        ]);
    }

    public function create(Request $request): View|ViewFactory
    {
        $classesTerapeuticas = ['' => 'Selecione a categoria'] + Medicamento::opcoesCategoriasTerapeuticas();
        $viasAdministracao = ['' => 'Selecione a via'] + Medicamento::opcoesViasAdministracao();
        $apresentacoes = ['' => 'Selecione a apresentação'] + Medicamento::opcoesApresentacoes();
        $restricoesIdade = ['' => 'Selecione a restrição etária'] + Medicamento::opcoesRestricoesIdade();
        $condicoesArmazenamento = ['' => 'Selecione a condição'] + Medicamento::opcoesCondicoesArmazenamento();
        $formasDispensacao = ['' => 'Selecione a forma de dispensação'] + Medicamento::opcoesFormasDispensacao();

        $opcoesEspecies = Especie::query()
            ->orderBy('nome')
            ->pluck('nome', 'id')
            ->toArray();

        return view('petshop.vet.medicines.create', [
            'therapeuticClasses' => $classesTerapeuticas,
            'routes' => $viasAdministracao,
            'presentations' => $apresentacoes,
            'ageRestrictions' => $restricoesIdade,
            'storageConditions' => $condicoesArmazenamento,
            'especiesOptions' => $opcoesEspecies,
            'dispensingOptions' => $formasDispensacao,
        ]);
    }

    public function edit(Medicamento $medicamento): View|ViewFactory
    {
        $medicamento->load(['produto.estoque', 'produto.estoqueLocais', 'especies']);

        $classesTerapeuticas = ['' => 'Selecione a categoria'] + Medicamento::opcoesCategoriasTerapeuticas();
        $viasAdministracao = ['' => 'Selecione a via'] + Medicamento::opcoesViasAdministracao();
        $apresentacoes = ['' => 'Selecione a apresentação'] + Medicamento::opcoesApresentacoes();
        $restricoesIdade = ['' => 'Selecione a restrição etária'] + Medicamento::opcoesRestricoesIdade();
        $condicoesArmazenamento = ['' => 'Selecione a condição'] + Medicamento::opcoesCondicoesArmazenamento();
        $formasDispensacao = ['' => 'Selecione a forma de dispensação'] + Medicamento::opcoesFormasDispensacao();

        $opcoesEspecies = Especie::query()
            ->orderBy('nome')
            ->pluck('nome', 'id')
            ->toArray();

        return view('petshop.vet.medicines.edit', [
            'medicine' => $this->formatarMedicamento($medicamento),
            'therapeuticClasses' => $classesTerapeuticas,
            'routes' => $viasAdministracao,
            'presentations' => $apresentacoes,
            'ageRestrictions' => $restricoesIdade,
            'storageConditions' => $condicoesArmazenamento,
            'especiesOptions' => $opcoesEspecies,
            'dispensingOptions' => $formasDispensacao,
        ]);
    }

    public function store(SalvarMedicamentoRequest $request): RedirectResponse
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $produto = Produto::with('medicamentoVeterinario')->findOrFail($request->integer('produto_id'));

        if ($produto->medicamentoVeterinario) {
            return back()
                ->withErrors(['produto_id' => 'O produto selecionado já está vinculado a outro medicamento.'])
                ->withInput();
        }

        $dados = $this->montarDadosMedicamento($request, $empresaId, $produto->id);

        try {
            DB::beginTransaction();

            $medicamento = Medicamento::create($dados);
            $medicamento->especies()->sync($request->input('especies', []));

            $produto->medicamento_veterinario_id = $medicamento->id;
            $produto->save();

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withErrors(['store' => 'Não foi possível salvar o medicamento. Tente novamente.'])
                ->withInput();
        }

        return redirect()
            ->route('vet.medicines.index')
            ->with('success', 'Medicamento salvo com sucesso.');
    }

    public function update(SalvarMedicamentoRequest $request, Medicamento $medicamento): RedirectResponse
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $novoProduto = Produto::with('medicamentoVeterinario')->findOrFail($request->integer('produto_id'));

        if ($novoProduto->medicamentoVeterinario && $novoProduto->medicamentoVeterinario->id !== $medicamento->id) {
            return back()
                ->withErrors(['produto_id' => 'O produto selecionado já está vinculado a outro medicamento.'])
                ->withInput();
        }

        $medicamento->load('produto');
        $dados = $this->montarDadosMedicamento($request, $empresaId, $novoProduto->id);
        if (is_null($empresaId) && ! is_null($medicamento->empresa_id)) {
            $dados['empresa_id'] = $medicamento->empresa_id;
        }

        try {
            DB::beginTransaction();

            if ($medicamento->produto && $medicamento->produto->id !== $novoProduto->id) {
                $medicamento->produto->medicamento_veterinario_id = null;
                $medicamento->produto->save();
            }

            $medicamento->fill($dados);
            $medicamento->save();

            $medicamento->especies()->sync($request->input('especies', []));

            $novoProduto->medicamento_veterinario_id = $medicamento->id;
            $novoProduto->save();

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withErrors(['update' => 'Não foi possível atualizar o medicamento. Tente novamente.'])
                ->withInput();
        }

        return redirect()
            ->route('vet.medicines.index', ['page' => $request->query('page', 1)])
            ->with('success', 'Medicamento atualizado com sucesso.');
    }

    public function destroy(Medicamento $medicamento): RedirectResponse
    {
        $medicamento->load('produto');

        try {
            DB::beginTransaction();

            if ($medicamento->produto) {
                $medicamento->produto->medicamento_veterinario_id = null;
                $medicamento->produto->save();
            }

            $medicamento->especies()->detach();
            $medicamento->delete();

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withErrors(['destroy' => 'Não foi possível excluir o medicamento. Tente novamente.']);
        }

        return redirect()
            ->route('vet.medicines.index', ['page' => request()->query('page', 1)])
            ->with('success', 'Medicamento excluído com sucesso.');
    }

    private function montarDadosMedicamento(SalvarMedicamentoRequest $request, ?int $empresaId, int $produtoId): array
    {
        return [
            'empresa_id' => $empresaId,
            'produto_id' => $produtoId,
            'nome_comercial' => $request->input('nome_comercial'),
            'nome_generico' => $request->input('nome_generico'),
            'classe_terapeutica' => $request->input('classe_terapeutica'),
            'classe_farmacologica' => $request->input('classe_farmacologica'),
            'classificacao_controle' => $request->input('classificacao_controle'),
            'via_administracao' => $request->input('via_administracao'),
            'apresentacao' => $request->input('apresentacao'),
            'concentracao' => $request->input('concentracao'),
            'forma_dispensacao' => $request->input('forma_dispensacao'),
            'dosagem' => $request->input('dosagem'),
            'frequencia' => $request->input('frequencia'),
            'duracao' => $request->input('duracao'),
            'restricao_idade' => $request->input('restricao_idade'),
            'condicao_armazenamento' => $request->input('condicao_armazenamento'),
            'validade' => $request->input('validade'),
            'fornecedor' => $request->input('fornecedor'),
            'sku' => $request->input('sku'),
            'indicacoes' => $request->input('indicacoes'),
            'contraindicacoes' => $request->input('contraindicacoes'),
            'efeitos_adversos' => $request->input('efeitos_adversos'),
            'interacoes' => $request->input('interacoes'),
            'monitoramento' => $request->input('monitoramento'),
            'orientacoes_tutor' => $request->input('orientacoes_tutor'),
            'observacoes' => $request->input('observacoes'),
            'status' => $this->normalizarStatus($request->input('status')),
        ];
    }

    private function buscarOpcoesFiltro(string $coluna, string $labelPadrao): array
    {
        $valores = Medicamento::query()
            ->select($coluna)
            ->whereNotNull($coluna)
            ->distinct()
            ->orderBy($coluna)
            ->pluck($coluna)
            ->filter()
            ->mapWithKeys(fn($valor) => [$valor => $valor])
            ->toArray();

        return ['' => $labelPadrao] + $valores;
    }

    private function formatarMedicamento(Medicamento $medicamento): array
    {
        $produto = $medicamento->produto;
        [$estoqueAtual, $estoqueMinimo] = $this->resolverEstoque($produto);
        [$statusEstoque, $corEstoque] = $this->resolverStatusEstoque($produto, $estoqueAtual, $estoqueMinimo);

        return [
            'id' => $medicamento->id,
            'product_id' => $produto?->id,
            'produto_id' => $produto?->id,
            'product' => $produto ? [
                'id' => $produto->id,
                'name' => $produto->nome,
                'label' => $this->formatarRotuloProduto($produto),
            ] : null,
            'product_label' => $this->formatarRotuloProduto($produto),
            'commercial_name' => $medicamento->nome_comercial,
            'nome_comercial' => $medicamento->nome_comercial,
            'generic_name' => $medicamento->nome_generico,
            'nome_generico' => $medicamento->nome_generico,
            'therapeutic_class' => $medicamento->classe_terapeutica,
            'classe_terapeutica' => $medicamento->classe_terapeutica,
            'pharmacological_class' => $medicamento->classe_farmacologica,
            'classe_farmacologica' => $medicamento->classe_farmacologica,
            'control_category' => $medicamento->classificacao_controle,
            'classificacao_controle' => $medicamento->classificacao_controle,
            'indications' => $medicamento->indicacoes,
            'indicacoes' => $medicamento->indicacoes,
            'species' => $medicamento->especies->pluck('nome')->filter()->values()->all(),
            'especies' => $medicamento->especies->pluck('id')->all(),
            'route' => $medicamento->via_administracao,
            'via_administracao' => $medicamento->via_administracao,
            'presentation' => $medicamento->apresentacao,
            'apresentacao' => $medicamento->apresentacao,
            'concentration' => $medicamento->concentracao,
            'concentracao' => $medicamento->concentracao,
            'dosage' => $medicamento->dosagem,
            'dosagem' => $medicamento->dosagem,
            'frequency' => $medicamento->frequencia,
            'frequencia' => $medicamento->frequencia,
            'duration' => $medicamento->duracao,
            'duracao' => $medicamento->duracao,
            'dispensing' => $medicamento->forma_dispensacao,
            'forma_dispensacao' => $medicamento->forma_dispensacao,
            'supplier' => $medicamento->fornecedor,
            'fornecedor' => $medicamento->fornecedor,
            'sku' => $medicamento->sku,
            'current_stock' => $this->normalizarQuantidade($estoqueAtual),
            'minimum_stock' => $this->normalizarQuantidade($estoqueMinimo),
            'storage' => $medicamento->condicao_armazenamento,
            'condicao_armazenamento' => $medicamento->condicao_armazenamento,
            'validity' => $medicamento->validade,
            'validade' => $medicamento->validade,
            'age_restrictions' => $medicamento->restricao_idade,
            'restricao_idade' => $medicamento->restricao_idade,
            'contraindications' => $medicamento->contraindicacoes,
            'contraindicacoes' => $medicamento->contraindicacoes,
            'adverse_effects' => $medicamento->efeitos_adversos,
            'efeitos_adversos' => $medicamento->efeitos_adversos,
            'interactions' => $medicamento->interacoes,
            'interacoes' => $medicamento->interacoes,
            'monitoring' => $medicamento->monitoramento,
            'monitoramento' => $medicamento->monitoramento,
            'tutor_guidance' => $medicamento->orientacoes_tutor,
            'orientacoes_tutor' => $medicamento->orientacoes_tutor,
            'notes' => $medicamento->observacoes,
            'observacoes' => $medicamento->observacoes,
            'status' => $medicamento->status ?? 'Ativo',
            'stock_status' => $statusEstoque,
            'stock_color' => $corEstoque,
            'created_at' => optional($medicamento->created_at)->format('d/m/Y'),
            'updated_at' => optional($medicamento->updated_at)->format('d/m/Y'),
        ];
    }

    private function resolverEstoque(?Produto $produto): array
    {
        if (! $produto) {
            return [0.0, 0.0];
        }

        $estoques = $produto->relationLoaded('estoqueLocais') ? $produto->estoqueLocais : collect();

        if ($estoques->isEmpty() && $produto->relationLoaded('estoque') && $produto->estoque) {
            $estoques = collect([$produto->estoque]);
        }

        $atual = (float) $estoques->sum(fn($estoque) => (float) ($estoque->quantidade ?? 0));
        $minimo = (float) ($produto->estoque_minimo ?? 0);

        return [$atual, $minimo];
    }

    private function resolverStatusEstoque(?Produto $produto, float $atual, float $minimo): array
    {
        if (! $produto || ! $produto->gerenciar_estoque) {
            return ['Disponível', 'success'];
        }

        if ($atual <= 0) {
            return ['Sem estoque', 'danger'];
        }

        if ($minimo > 0 && $atual <= $minimo) {
            return ['Estoque baixo', 'warning'];
        }

        return ['Disponível', 'success'];
    }

    private function formatarRotuloProduto(?Produto $produto): ?string
    {
        if (! $produto) {
            return null;
        }

        $rotulo = $produto->nome;

        if (! is_null($produto->valor_unitario)) {
            $rotulo .= ' - R$ ' . __moeda($produto->valor_unitario);
        }

        return trim($rotulo);
    }

    private function normalizarStatus(?string $status): string
    {
        $permitidos = ['Ativo', 'Inativo'];

        if ($status && in_array($status, $permitidos, true)) {
            return $status;
        }

        return 'Ativo';
    }

    private function normalizarQuantidade(float $quantidade): string
    {
        if (abs($quantidade - round($quantidade)) < 0.0001) {
            return (string) (int) round($quantidade);
        }

        $formatado = number_format($quantidade, 3, '.', '');

        return rtrim(rtrim($formatado, '0'), '.');
    }
}