<?php

namespace App\Http\Controllers\Petshop\Vacinas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petshop\SalvarVacinaRequest;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Vacina;
use App\Models\Produto;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Throwable;

class VacinaController extends Controller
{
    public function index(Request $request): View|ViewFactory
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;

        $query = Vacina::query()
            ->with(['produto.estoque', 'produto.estoqueLocais', 'especies'])
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhere('categoria', 'like', "%{$search}%")
                    ->orWhere('coberturas', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%")
                    ->orWhere('grupo_vacinal', 'like', "%{$search}%")
                    ->orWhere('fabricante', 'like', "%{$search}%");
            });
        }

        if ($request->filled('group')) {
            $query->where('grupo_vacinal', $request->string('group'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $speciesFilter = $this->normalizarFiltroSpecies($request->input('species'));

        if (! empty($speciesFilter)) {
            $query->whereHas('especies', fn($q) => $q->whereIn('animais_especies.id', $speciesFilter));
        }

        $vacinas = $query
            ->orderBy('nome')
            ->get()
            ->map(fn(Vacina $vacina) => $this->formatarVacina($vacina));

        $opcoesGrupos = ['' => 'Todos os grupos'] + Vacina::opcoesGrupos();
        $opcoesStatus = ['' => 'Todos os status'] + Vacina::opcoesStatus();
        $opcoesEspecies = ['' => 'Todas as espécies'] + $this->buscarOpcoesEspecies();

        return view('vacina.vacinas.index', [
            'vaccines' => $vacinas,
            'groupOptions' => $opcoesGrupos,
            'speciesOptions' => $opcoesEspecies,
            'statusOptions' => $opcoesStatus,
            'selectedSpeciesFilter' => $speciesFilter,
        ]);
    }

    public function create(): View|ViewFactory
    {
        return view('vacina.vacinas.create', $this->montarDadosFormulario());
    }

    public function store(SalvarVacinaRequest $request): RedirectResponse
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $produto = Produto::findOrFail($request->integer('product_id'));

        $dados = $this->montarDadosVacina($request, $empresaId, $produto);

        try {
            DB::beginTransaction();

            $vacina = Vacina::create($dados);
            $vacina->especies()->sync($request->input('species', []));

            $produto->vacina_veterinaria_id = $vacina->id;
            $produto->save();

            DB::commit();
        } catch (QueryException $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withErrors([
                    'store' => $this->mensagemErroConstraintProduto(
                        $exception,
                        'Não foi possível salvar a vacina. Tente novamente.'
                    ),
                ])
                ->withInput();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withErrors(['store' => 'Não foi possível salvar a vacina. Tente novamente.'])
                ->withInput();
        }

        return redirect()
            ->route('vacina.vacinas.index')
            ->with('success', 'Vacina salva com sucesso.');
    }

    public function edit(Vacina $vacina): View|ViewFactory
    {
        $vacina->load(['produto.estoque', 'produto.estoqueLocais', 'especies']);

        $dadosFormulario = $this->montarDadosFormulario($this->formatarVacina($vacina));

        return view('vacina.vacinas.edit', $dadosFormulario);
    }

    public function update(SalvarVacinaRequest $request, Vacina $vacina): RedirectResponse
    {
        $produto = Produto::findOrFail($request->integer('product_id'));

        $dados = $this->montarDadosVacina($request, $vacina->empresa_id, $produto);

        try {
            DB::beginTransaction();

            $vacina->update($dados);
            $vacina->especies()->sync($request->input('species', []));

            $produto->vacina_veterinaria_id = $vacina->id;
            $produto->save();

            DB::commit();
        } catch (QueryException $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withErrors([
                    'update' => $this->mensagemErroConstraintProduto(
                        $exception,
                        'Não foi possível atualizar a vacina. Tente novamente.'
                    ),
                ])
                ->withInput();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()
                ->withErrors(['update' => 'Não foi possível atualizar a vacina. Tente novamente.'])
                ->withInput();
        }

        return redirect()
            ->route('vacina.vacinas.index')
            ->with('success', 'Vacina atualizada com sucesso.');
    }

    public function destroy(Vacina $vacina): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $vacina->especies()->detach();

            Produto::where('vacina_veterinaria_id', $vacina->id)
                ->update(['vacina_veterinaria_id' => null]);

            $vacina->delete();

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->withErrors(['delete' => 'Não foi possível remover a vacina.']);
        }

        return redirect()
            ->route('vacina.vacinas.index')
            ->with('success', 'Vacina removida com sucesso.');
    }

    private function montarDadosFormulario(?array $vacinaFormatada = null): array
    {
        $statusOptions = Vacina::opcoesStatus();
        $groupOptions = ['' => 'Selecione o grupo vacinal'] + Vacina::opcoesGrupos();
        $categoryOptions = ['' => 'Selecione a categoria'] + Vacina::opcoesCategorias();
        $manufacturerOptions = ['' => 'Selecione o laboratório'] + Vacina::opcoesFabricantes();
        $presentationOptions = ['' => 'Selecione a apresentação'] + Vacina::opcoesApresentacoes();
        $routeOptions = ['' => 'Selecione a via de aplicação'] + Vacina::opcoesViasAdministracao();
        $applicationSiteOptions = ['' => 'Selecione o local sugerido'] + Vacina::opcoesLocaisAplicacao();
        $ageOptions = ['' => 'Selecione a idade mínima'] + Vacina::opcoesIdadesMinimas();
        $boosterOptions = ['' => 'Selecione o intervalo de reforço'] + Vacina::opcoesIntervalosReforco();
        $storageConditionOptions = ['' => 'Selecione a condição de armazenamento'] + Vacina::opcoesCondicoesArmazenamento();
        $documentationOptions = Vacina::opcoesDocumentos();

        $especiesOptions = $this->buscarOpcoesEspecies();

        return [
            'vaccine' => $vacinaFormatada,
            'statusOptions' => $statusOptions,
            'groupOptions' => $groupOptions,
            'categoryOptions' => $categoryOptions,
            'manufacturerOptions' => $manufacturerOptions,
            'presentationOptions' => $presentationOptions,
            'routeOptions' => $routeOptions,
            'applicationSiteOptions' => $applicationSiteOptions,
            'ageOptions' => $ageOptions,
            'boosterOptions' => $boosterOptions,
            'storageConditionOptions' => $storageConditionOptions,
            'documentationOptions' => $documentationOptions,
            'speciesOptions' => $especiesOptions,
        ];
    }

    private function montarDadosVacina(SalvarVacinaRequest $request, ?int $empresaId, Produto $produto): array
    {
        $documentos = array_values(array_filter($request->input('documentation', [])));
        $tags = array_values(array_filter(array_map(static fn(string $valor) => trim($valor), explode(',', (string) $request->input('tags', '')))));

        $coberturas = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('coverage', '')))
            ->map(fn(string $linha) => trim($linha))
            ->filter()
            ->implode("\n");

        return [
            'empresa_id' => $empresaId,
            'produto_id' => $produto->id,
            'codigo' => $request->input('code'),
            'nome' => $this->resolverNomeVacina($request->input('code'), $produto),
            'status' => $this->normalizarStatus($request->input('status')),
            'grupo_vacinal' => $request->input('group'),
            'categoria' => $request->input('category'),
            'fabricante' => $request->input('manufacturer'),
            'registro_mapa' => $request->input('registration'),
            'apresentacao' => $request->input('presentation'),
            'concentracao' => $request->input('concentration'),
            'idade_minima' => $request->input('minimum_age'),
            'intervalo_reforco' => $request->input('booster_interval'),
            'dosagem' => $request->input('dosage'),
            'via_administracao' => $request->input('route'),
            'local_aplicacao' => $request->input('application_site'),
            'coberturas' => $coberturas !== '' ? $coberturas : null,
            'protocolo_inicial' => $request->input('protocol_primary'),
            'protocolo_reforco' => $request->input('protocol_booster'),
            'protocolo_revacinar' => $request->input('protocol_revaccination'),
            'requisitos_pre_vacinacao' => $request->input('pre_vaccination_requirements'),
            'orientacoes_pos_vacinacao' => $request->input('post_vaccination_guidance'),
            'efeitos_adversos' => $request->input('adverse_effects'),
            'contraindicacoes' => $request->input('contraindications'),
            'validade_fechada' => $request->input('validity_closed'),
            'validade_aberta' => $request->input('validity_opened'),
            'condicao_armazenamento' => $request->input('storage_condition'),
            'temperatura_armazenamento' => $request->input('storage_temperature'),
            'alertas_armazenamento' => $request->input('storage_alerts'),
            'limite_perdas' => $request->input('inventory_wastage_limit'),
            'tempo_reposicao' => $request->input('inventory_lead_time'),
            'documentos' => $documentos ?: null,
            'tags' => $tags ?: null,
            'observacoes' => $request->input('notes'),
        ];
    }

    private function formatarVacina(Vacina $vacina): array
    {
        $produto = $vacina->produto;
        [$estoqueAtual, $estoqueMinimo, $estoqueSeguranca, $reservado] = $this->resolverEstoque($produto);

        $status = Vacina::dadosStatus($vacina->status ?? 'ativa');
        $grupo = Vacina::opcoesGrupos()[$vacina->grupo_vacinal] ?? $vacina->grupo_vacinal;
        $via = Vacina::opcoesViasAdministracao()[$vacina->via_administracao] ?? $vacina->via_administracao;
        $local = Vacina::opcoesLocaisAplicacao()[$vacina->local_aplicacao] ?? $vacina->local_aplicacao;
        $idade = Vacina::opcoesIdadesMinimas()[$vacina->idade_minima] ?? $vacina->idade_minima;
        $reforco = Vacina::opcoesIntervalosReforco()[$vacina->intervalo_reforco] ?? $vacina->intervalo_reforco;

        $coberturas = collect(preg_split('/\r\n|\r|\n/', (string) $vacina->coberturas))
            ->map(fn(string $linha) => trim($linha))
            ->filter()
            ->values()
            ->all();

        $tags = collect($vacina->tags ?? [])
            ->map(fn($valor) => is_string($valor) ? trim($valor) : $valor)
            ->filter()
            ->values()
            ->all();

        $documentos = collect($vacina->documentos ?? [])
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $vacina->id,
            'product_id' => $produto?->id,
            'produto_id' => $produto?->id,
            'product' => $produto ? [
                'id' => $produto->id,
                'name' => $produto->nome,
                'label' => $this->formatarRotuloProduto($produto),
            ] : null,
            'product_label' => $this->formatarRotuloProduto($produto),
            'code' => $vacina->codigo,
            'name' => $vacina->nome,
            'group' => $vacina->grupo_vacinal,
            'group_label' => $grupo,
            'status' => $vacina->status,
            'status_label' => $status['label'] ?? $vacina->status,
            'status_color' => $status['color'] ?? 'secondary',
            'category' => $vacina->categoria,
            'manufacturer' => $vacina->fabricante,
            'registration' => $vacina->registro_mapa,
            'presentation' => $vacina->apresentacao,
            'concentration' => $vacina->concentracao,
            'species' => $vacina->especies->pluck('nome', 'id')->toArray(),
            'species_keys' => $vacina->especies->pluck('id')->all(),
            'coverage' => $coberturas,
            'protocol' => [
                'primary' => $vacina->protocolo_inicial,
                'booster' => $vacina->protocolo_reforco,
                'revaccination' => $vacina->protocolo_revacinar,
            ],
            'minimum_age' => $vacina->idade_minima,
            'minimum_age_label' => $idade,
            'booster_interval' => $vacina->intervalo_reforco,
            'booster_interval_label' => $reforco,
            'dosage' => $vacina->dosagem,
            'route' => $vacina->via_administracao,
            'route_label' => $via,
            'application_site' => $vacina->local_aplicacao,
            'application_site_label' => $local,
            'storage_condition' => $vacina->condicao_armazenamento,
            'storage_condition_label' => Vacina::opcoesCondicoesArmazenamento()[$vacina->condicao_armazenamento] ?? $vacina->condicao_armazenamento,
            'storage_temperature' => $vacina->temperatura_armazenamento,
            'storage_alerts' => $vacina->alertas_armazenamento,
            'validity_closed' => $vacina->validade_fechada,
            'validity_opened' => $vacina->validade_aberta,
            'pre_vaccination_requirements' => $vacina->requisitos_pre_vacinacao,
            'post_vaccination_guidance' => $vacina->orientacoes_pos_vacinacao,
            'adverse_effects' => $vacina->efeitos_adversos,
            'contraindications' => $vacina->contraindicacoes,
            'documentation' => $documentos,
            'notes' => $vacina->observacoes,
            'tags' => $tags,
            'inventory' => [
                'current_stock' => $this->normalizarQuantidade($estoqueAtual),
                'minimum_stock' => $this->normalizarQuantidade($estoqueMinimo),
                'safety_stock' => $this->normalizarQuantidade($estoqueSeguranca),
                'reserved_doses' => $this->normalizarQuantidade($reservado),
                'wastage_limit' => $vacina->limite_perdas,
                'lead_time' => $vacina->tempo_reposicao,
            ],
        ];
    }

    private function buscarOpcoesEspecies(): array
    {
        return Especie::query()
            ->orderBy('nome')
            ->pluck('nome', 'id')
            ->toArray();
    }

    /**
     * @param  mixed  $species
     * @return array<int, int>
     */
    private function normalizarFiltroSpecies(mixed $species): array
    {
        if (is_null($species)) {
            return [];
        }

        if (is_string($species)) {
            $decoded = json_decode($species, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $species = $decoded;
            } elseif ($species !== '') {
                $species = [$species];
            }
        }

        if (is_int($species)) {
            $species = [$species];
        }

        if (! is_array($species)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn($value) => (int) $value, $species),
            static fn($id) => $id > 0
        ));
    }

    private function resolverEstoque(?Produto $produto): array
    {
        if (! $produto) {
            return [0.0, 0.0, 0.0, 0.0];
        }

        $estoques = $produto->relationLoaded('estoqueLocais') ? $produto->estoqueLocais : collect();

        if ($estoques->isEmpty() && $produto->relationLoaded('estoque') && $produto->estoque) {
            $estoques = collect([$produto->estoque]);
        }

        $atual = (float) $estoques->sum(fn($estoque) => (float) ($estoque->quantidade ?? 0));
        $reservado = (float) $estoques->sum(fn($estoque) => (float) ($estoque->reservado ?? 0));
        $seguranca = (float) $estoques->sum(fn($estoque) => (float) ($estoque->reposicao ?? 0));
        $minimo = (float) ($produto->estoque_minimo ?? 0);

        return [$atual, $minimo, $seguranca, $reservado];
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
        $chaves = array_keys(Vacina::opcoesStatus());

        if ($status && in_array($status, $chaves, true)) {
            return $status;
        }

        return 'ativa';
    }

    private function normalizarQuantidade(float $quantidade): string
    {
        if (abs($quantidade - round($quantidade)) < 0.0001) {
            return (string) (int) round($quantidade);
        }

        $formatado = number_format($quantidade, 3, '.', '');

        return rtrim(rtrim($formatado, '0'), '.');
    }

    private function resolverNomeVacina(?string $codigo, Produto $produto): string
    {
        if ($produto->nome) {
            return $produto->nome;
        }

        if ($codigo) {
            return Str::upper($codigo);
        }

        return 'Vacina sem nome';
    }

    private function mensagemErroConstraintProduto(QueryException $exception, string $mensagemPadrao): string
    {
        if ($this->isProdutoIdUniqueConstraint($exception)) {
            return 'Existe uma constraint de unicidade para o produto no banco (migration 2025_11_20_000000_drop_unique_produto_id_from_petshop_vet_vacinas.php). Execute `php artisan migrate` ou rode especificamente essa migration e tente novamente.';
        }

        return $mensagemPadrao;
    }

    private function isProdutoIdUniqueConstraint(QueryException $exception): bool
    {
        $mensagem = (string) ($exception->errorInfo[2] ?? $exception->getMessage());

        return str_contains($mensagem, 'petshop_vet_vacinas_produto_id_unique')
            || (str_contains($mensagem, 'produto_id') && str_contains($mensagem, 'unique'));
    }
}
