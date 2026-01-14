<?php

declare(strict_types=1);

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\ModeloAtendimento;
use App\Services\Petshop\Vet\ModeloAtendimentoService;
use App\Support\Petshop\Vet\ModeloAtendimentoOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ModeloAtendimentoController extends Controller
{
    protected ModeloAtendimentoService $modelo_atendimento_service;

    public function __construct(ModeloAtendimentoService $modelo_atendimento_service)
    {
        $this->modelo_atendimento_service = $modelo_atendimento_service;
    }

    public function index(Request $request): View
    {
        $empresaId = (int) ($request->empresa_id ?? 0);
        abort_unless($empresaId, 403, 'Empresa não encontrada para o usuário autenticado.');

        $data = ModeloAtendimento::query()
            ->where('empresa_id', $empresaId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $termo = Str::of((string) $request->input('search'))->trim()->toString();

                if ($termo === '') {
                    return;
                }

                $query->where(function ($sub_query) use ($termo) {
                    $sub_query
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
                    $categorias_padrao = array_keys(ModeloAtendimentoOptions::categories());

                    $query->where(function ($sub_query) use ($categorias_padrao) {
                        $sub_query
                            ->where('category', 'personalizado')
                            ->orWhere(function ($custom_query) use ($categorias_padrao) {
                                $custom_query
                                    ->whereNotNull('category')
                                    ->whereNotIn('category', $categorias_padrao);
                            });
                    });

                    return;
                }

                $query->where('category', $categoria);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->string('status')->toString();

                if (in_array($status, ModeloAtendimentoOptions::statuses(), true)) {
                    $query->where('status', $status);
                }
            })
            ->orderByDesc('updated_at')
            ->paginate(env("PAGINACAO"))
            ->appends($request->all());

        $category_options = ['' => 'Todas'] + ModeloAtendimentoOptions::categories();
        $status_options = ['' => 'Todos'] + ModeloAtendimentoOptions::statusOptions();

        $missing_templates = $this->getMissingDefaultModeloAtendimentoTemplate($empresaId);

        return view('petshop.vet.modelos_atendimento.index', [
            'data' => $data,
            'category_options' => $category_options,
            'status_options' => $status_options,
            'missing_templates' => $missing_templates,
        ]);
    }

    public function create(): View
    {
        $category_options = ModeloAtendimentoOptions::categories();
        $status_options =  ModeloAtendimentoOptions::statusOptions();

        return view('petshop.vet.modelos_atendimento.create', [
            'category_options' => $category_options,
            'status_options' => $status_options,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'empresa_id' => $request->empresa_id,
            'status' => 'ativo',
        ]);

        $this->_validate($request);

        try {
            $user = auth()->user();
            
            if ($user) {
                $request->merge(
                    [
                        'created_by' => $user->id,
                        'updated_by' => $user->id
                    ]
                );
            }

            DB::transaction(function () use ($request) {
                ModeloAtendimento::create($request->all());
            });

            session()->flash("flash_sucesso", "Modelo de atendimento cadastrado!");
            return redirect()->route('vet.modelos-atendimento.index');
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);

            return redirect()
                ->back()
                ->withInput();
        }
    }

    public function edit(Request $request, int $id): View
    {
        $item = ModeloAtendimento::findOrFail($id);

        $category_options = ModeloAtendimentoOptions::categories();
        $status_options =  ModeloAtendimentoOptions::statusOptions();

        return view('petshop.vet.modelos_atendimento.edit', [
            'item' => $item,
            'category_options' => $category_options,
            'status_options' => $status_options,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->_validate($request);

        try {
            $item = ModeloAtendimento::findOrFail($id);

            $user = auth()->user();

            if ($user) {
                $request->merge(
                    [
                        'updated_by' => $user->id
                    ]
                );
            }

            DB::transaction(function () use ($item, $request) {
                $item->update($request->all());
            });

            session()->flash("flash_sucesso", "Modelo de atendimento atualizado!");
            return redirect()->route('vet.modelos-atendimento.index');
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
            
            return redirect()
                ->back()
                ->withInput();
        }
    }

    private function _validate(Request $request): void
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:' . implode(',', ModeloAtendimentoOptions::statuses())],
            'content' => ['required', 'string'],
        ];

        $messages = [
            'title.required' => 'O campo Título é obrigatório.',
            'status.required' => 'O campo Status é obrigatório.',
            'content.required' => 'O campo Conteúdo é obrigatório.',
        ];

        $this->validate($request, $rules, $messages);
    }

    /**
     * Pega os templates de modelos de atendimento padrão que ainda não foram
     * adicionados na base de dados da empresa.
     * 
     * @param int $empresa_id Empresa que está buscando
     * 
     * @return object Templates que ainda não foram adicionados
     */
    private function getMissingDefaultModeloAtendimentoTemplate(int $empresa_id): object
    {
        $modelos_atendimento_templates = $this->modelo_atendimento_service->getDefaultTemplates();

        $missing_templates = null;
        foreach ($modelos_atendimento_templates as $template) {
            $exists = ModeloAtendimento::query()
                ->where('empresa_id', $empresa_id)
                ->where('title', $template['title'])
                ->where('category', $template['category'])
                ->exists();


            if (!$exists) {
                $missing_templates[] = $template;
            }
        }
        

        return collect($missing_templates);
    }
}
