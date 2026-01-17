<?php

declare(strict_types=1);

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petshop\StoreModeloAvaliacaoRequest;
use App\Http\Requests\Petshop\UpdateModeloAvaliacaoRequest;
use App\Models\Petshop\ModeloAvaliacao;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ModelosAvaliacaoController extends Controller
{
    public function index(Request $request): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        $modelos = ModeloAvaliacao::query()
            ->where('empresa_id', $empresaId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $termo = Str::of((string) $request->input('search'))->trim()->toString();

                if ($termo === '') {
                    return;
                }

                $query->where(function ($subQuery) use ($termo) {
                    $subQuery
                        ->where('title', 'like', "%{$termo}%")
                        ->orWhere('notes', 'like', "%{$termo}%");
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $categoria = $request->string('category')->toString();

                if ($categoria === '') {
                    return;
                }

                if ($categoria === 'personalizado') {
                    $categoriasPadrao = array_keys(ModeloAvaliacao::categories());

                    $query->where(function ($subQuery) use ($categoriasPadrao) {
                        $subQuery
                            ->where('category', 'personalizado')
                            ->orWhere(function ($customQuery) use ($categoriasPadrao) {
                                $customQuery
                                    ->whereNotNull('category')
                                    ->whereNotIn('category', $categoriasPadrao);
                            });
                    });

                    return;
                }

                $query->where('category', $categoria);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->string('status')->toString();

                if (in_array($status, ModeloAvaliacao::statuses(), true)) {
                    $query->where('status', $status);
                }
            })
            ->orderByDesc('updated_at')
            ->paginate(env("PAGINACAO"))
            ->appends($request->all());

        $modelos->getCollection()->transform(
            fn(ModeloAvaliacao $modelo) => $this->transformarModelo($modelo)
        );

        return view('petshop.vet.modelos_avaliacao.index', [
            'modelosAvaliacao' => $modelos,
        ]);
    }

    public function create(): View|ViewFactory
    {
        return view('petshop.vet.modelos_avaliacao.create');
    }

    public function store(StoreModeloAvaliacaoRequest $request): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();
        $userId = Auth::id();

        abort_unless($userId, 403, 'Usuário não autenticado.');

        $campos = $this->normalizarCampos($request->input('fields', []));

        if ($campos === []) {
            return back()
                ->withInput()
                ->withErrors(['fields' => 'Configure ao menos um campo para salvar o modelo.']);
        }

        $titulo = Str::of($request->input('title'))->trim()->toString();
        $categoria = $request->filled('category')
            ? Str::of((string) $request->input('category'))->trim()->toString()
            : null;
        $categoriaPersonalizada = $request->filled('custom_category')
            ? Str::of((string) $request->input('custom_category'))->trim()->toString()
            : null;
        $observacoes = $request->filled('notes')
            ? Str::of((string) $request->input('notes'))->trim()->toString()
            : null;

        if ($categoria === '') {
            $categoria = null;
        }

        if ($categoriaPersonalizada !== null && $categoriaPersonalizada === '') {
            $categoriaPersonalizada = null;
        }

        if ($categoria === 'personalizado' && $categoriaPersonalizada !== null) {
            $categoria = Str::substr($categoriaPersonalizada, 0, 255);
        }

        if ($observacoes === '') {
            $observacoes = null;
        }

        try {
            DB::transaction(function () use ($empresaId, $titulo, $categoria, $observacoes, $campos, $userId) {
                ModeloAvaliacao::create([
                    'empresa_id' => $empresaId,
                    'title' => $titulo,
                    'category' => $categoria,
                    'notes' => $observacoes,
                    'fields' => $campos,
                    'status' => ModeloAvaliacao::STATUS_ACTIVE,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            });

            session()->flash("flash_sucesso", "Modelo de avaliação cadastrado!");
        } catch (Throwable $exception) {
            session()->flash("flash_erro", "Algo deu errado: " . $exception->getMessage());
            __saveLogError($exception, request()->empresa_id);

            return back()
                ->withInput()
                ->withErrors(['store' => 'Não foi possível salvar o modelo de avaliação. Tente novamente.']);
        }

        return redirect()->route('vet.assessment-models.index');
    }

    public function show(int $modeloId): View|ViewFactory
    {
        $modelo = $this->buscarModelo($modeloId);

        return view('petshop.vet.modelos_avaliacao.show', [
            'modelo' => $modelo,
            'detalhesModelo' => $this->formatarModeloDetalhado($modelo),
        ]);
    }

    public function edit(int $modeloId): View|ViewFactory
    {
        $modelo = $this->buscarModelo($modeloId);

        return view('petshop.vet.modelos_avaliacao.edit', [
            'modelo' => $modelo,
            'categorias' => ModeloAvaliacao::categories(),
            'statusOptions' => ModeloAvaliacao::statusOptions(),
        ]);
    }

    public function update(UpdateModeloAvaliacaoRequest $request, int $modeloId): RedirectResponse
    {
        $modelo = $this->buscarModelo($modeloId);
        $userId = Auth::id();

        abort_unless($userId, 403, 'Usuário não autenticado.');

        $campos = $this->normalizarCampos($request->input('fields', []));

        if ($campos === []) {
            return back()
                ->withInput()
                ->withErrors(['fields' => 'Configure ao menos um campo para salvar o modelo.']);
        }

        $titulo = Str::of($request->input('title'))->trim()->toString();
        $categoria = $request->filled('category')
            ? Str::of((string) $request->input('category'))->trim()->toString()
            : null;
        $categoriaPersonalizada = $request->filled('custom_category')
            ? Str::of((string) $request->input('custom_category'))->trim()->toString()
            : null;
        $observacoes = $request->filled('notes')
            ? Str::of((string) $request->input('notes'))->trim()->toString()
            : null;
        $status = $request->string('status')->toString();

        if ($categoria === '') {
            $categoria = null;
        }

        if ($categoriaPersonalizada !== null && $categoriaPersonalizada === '') {
            $categoriaPersonalizada = null;
        }

        if ($categoria === 'personalizado' && $categoriaPersonalizada !== null) {
            $categoria = Str::substr($categoriaPersonalizada, 0, 255);
        }

        if ($observacoes === '') {
            $observacoes = null;
        }

        if (! in_array($status, ModeloAvaliacao::statuses(), true)) {
            $status = $modelo->status ?? ModeloAvaliacao::STATUS_ACTIVE;
        }

        try {
            DB::transaction(function () use ($modelo, $titulo, $categoria, $observacoes, $campos, $status, $userId) {
                $modelo->update([
                    'title' => $titulo,
                    'category' => $categoria,
                    'notes' => $observacoes,
                    'fields' => $campos,
                    'status' => $status,
                    'updated_by' => $userId,
                ]);
            });

            session()->flash("flash_sucesso", "Modelo de avaliação atualizado!");
        } catch (Throwable $exception) {
            session()->flash("flash_erro", "Algo deu errado: " . $exception->getMessage());
            __saveLogError($exception, request()->empresa_id);

            return back()
                ->withInput()
                ->withErrors(['update' => 'Não foi possível atualizar o modelo de avaliação. Tente novamente.']);
        }

        $page = $request->query('page');

        return redirect()->route('vet.assessment-models.index', $page ? ['page' => $page] : []);
    }

    private function normalizarCampos(array $fields): array
    {
        $labels = $fields['label'] ?? [];
        $tipos = $fields['type'] ?? [];

        $resultado = [];

        foreach ($labels as $indice => $label) {
            $nomeCampo = Str::of((string) $label)->trim()->toString();
            $tipoCampo = $tipos[$indice] ?? null;

            if ($nomeCampo === '' || ! is_string($tipoCampo) || $tipoCampo === '') {
                continue;
            }

            $tipoCampo = Str::of($tipoCampo)->trim()->toString();

            if (! in_array($tipoCampo, ModeloAvaliacao::fieldTypes(), true)) {
                continue;
            }

            $configuracoes = $this->extrairConfiguracoes($fields, $tipoCampo, $indice);

            $resultado[] = [
                'label' => $nomeCampo,
                'type' => $tipoCampo,
                'config' => $configuracoes,
            ];
        }

        return $resultado;
    }

    private function extrairConfiguracoes(array $fields, string $tipo, int $indice): array
    {
        $configKeys = ModeloAvaliacao::configKeysForType($tipo);
        $config = [];

        foreach ($configKeys as $key) {
            $valor = $fields[$key][$indice] ?? null;
            $normalizado = $this->normalizarValorConfiguracao($tipo, $key, $valor);

            if ($normalizado === null) {
                continue;
            }

            if (is_array($normalizado) && $normalizado === []) {
                continue;
            }

            $config[$key] = $normalizado;
        }

        if ($tipo === 'checkbox' && ! array_key_exists('checkbox_default', $config)) {
            $config['checkbox_default'] = 'unchecked';
        }

        return $config;
    }

    private function normalizarValorConfiguracao(string $tipo, string $chave, mixed $valor): mixed
    {
        if (is_string($valor) && $chave !== 'rich_text_default') {
            $valor = Str::of($valor)->trim()->toString();
        }

        if ($chave === 'rich_text_default' && is_string($valor)) {
            $valor = Str::of($valor)->trim()->toString();
        }

        if ($valor === null) {
            return null;
        }

        if ($valor === '' && $chave !== 'checkbox_default') {
            return null;
        }

        return match ($chave) {
            'number_min', 'number_max' => is_numeric($valor) ? (float) $valor : null,
            'integer_min', 'integer_max' => is_numeric($valor) ? (int) $valor : null,
            'file_max_size' => is_numeric($valor) ? (int) $valor : null,
            'select_options', 'multi_select_options', 'checkbox_group_options', 'radio_group_options' => $this->normalizarOpcoes((string) $valor),
            'checkbox_default' => in_array($valor, ['checked', 'unchecked'], true) ? $valor : 'unchecked',
            default => $valor,
        };
    }

    private function normalizarOpcoes(string $valor): array
    {
        $linhas = preg_split("/(\r\n|\r|\n)/", $valor) ?: [];
        $opcoes = [];

        foreach ($linhas as $linha) {
            $opcao = Str::of($linha)->trim()->toString();

            if ($opcao === '') {
                continue;
            }

            $opcoes[] = $opcao;
        }

        return $opcoes;
    }

    private function transformarModelo(ModeloAvaliacao $modelo): array
    {
        return [
            'id' => $modelo->id,
            'title' => $modelo->title,
            'category' => ModeloAvaliacao::categoryLabel($modelo->category) ?? '—',
            'updated_at' => optional($modelo->updated_at)?->format('d/m/Y H:i'),
            'status' => $this->rotuloStatus($modelo->status),
            'status_class' => $this->classeStatus($modelo->status),
        ];
    }

    private function rotuloStatus(?string $status): string
    {
        return match ($status) {
            ModeloAvaliacao::STATUS_ACTIVE => 'Ativo',
            ModeloAvaliacao::STATUS_INACTIVE => 'Inativo',
            default => '—',
        };
    }

    private function classeStatus(?string $status): string
    {
        return match ($status) {
            ModeloAvaliacao::STATUS_ACTIVE => 'badge bg-success',
            ModeloAvaliacao::STATUS_INACTIVE => 'badge bg-secondary',
            default => 'badge bg-light text-dark',
        };
    }

    private function buscarModelo(int $modeloId): ModeloAvaliacao
    {
        $empresaId = $this->getEmpresaId();

        return ModeloAvaliacao::where('empresa_id', $empresaId)
            ->findOrFail($modeloId);
    }

    private function formatarModeloDetalhado(ModeloAvaliacao $modelo): array
    {
        $campos = collect($modelo->fields ?? [])
            ->filter(fn($campo) => is_array($campo))
            ->values()
            ->map(function (array $campo, int $indice) {
                $label = Str::of((string) ($campo['label'] ?? ''))
                    ->trim()
                    ->toString();

                if ($label === '') {
                    $label = 'Campo ' . ($indice + 1);
                }

                $tipo = is_string($campo['type'] ?? null)
                    ? $campo['type']
                    : '';

                return [
                    'label' => $label,
                    'type' => $tipo,
                    'type_label' => ModeloAvaliacao::fieldTypeLabel($tipo),
                    'configuracoes' => $this->formatarConfiguracoesParaVisualizacao(
                        $tipo,
                        is_array($campo['config'] ?? null) ? $campo['config'] : []
                    ),
                ];
            })
            ->all();

        return [
            'title' => $modelo->title,
            'category_label' => ModeloAvaliacao::categoryLabel($modelo->category) ?? '—',
            'notes' => $modelo->notes,
            'status_label' => $this->rotuloStatus($modelo->status),
            'status_class' => $this->classeStatus($modelo->status),
            'updated_at' => optional($modelo->updated_at)?->format('d/m/Y H:i'),
            'created_at' => optional($modelo->created_at)?->format('d/m/Y H:i'),
            'fields' => $campos,
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array{label: string, value: string, is_html: bool}>
     */
    private function formatarConfiguracoesParaVisualizacao(string $tipo, array $config): array
    {
        $resultado = [];

        foreach ($config as $chave => $valor) {
            if ($valor === null || $valor === '' || (is_array($valor) && $valor === [])) {
                continue;
            }

            $resultado[] = [
                'label' => ModeloAvaliacao::configLabel((string) $chave, $tipo),
                'value' => $this->formatarValorConfiguracaoParaVisualizacao((string) $chave, $valor),
                'is_html' => $chave === 'rich_text_default',
            ];
        }

        return $resultado;
    }

    private function formatarValorConfiguracaoParaVisualizacao(string $chave, mixed $valor): string
    {
        if (is_array($valor)) {
            return implode(', ', array_map(static fn($item) => (string) $item, $valor));
        }

        if ($chave === 'checkbox_default') {
            return $valor === 'checked' ? 'Marcado por padrão' : 'Desmarcado por padrão';
        }

        return (string) $valor;
    }

    private function getEmpresaId(): int
    {
        $empresaId = request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;

        abort_unless($empresaId, 403, 'Empresa não encontrada para o usuário autenticado.');

        return (int) $empresaId;
    }
}
