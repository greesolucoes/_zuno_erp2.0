<?php

namespace App\Http\Controllers\Petshop\Agendamento;

use App\Http\Controllers\Controller;
use App\Models\CategoriaServico;
use App\Models\Funcionamento;
use App\Models\Role;
use App\Models\Servico;
use App\Models\User;
use App\Models\Nfce;
use App\Models\UsuarioEmpresa;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Agendamento;
use App\Models\ItemAgendamento;
use App\Models\CategoriaProduto;
use App\Models\Caixa;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ConfigGeral;
use App\Models\OrdemServico;
use App\Models\Petshop\Estetica;
use App\Models\Petshop\Plano;
use App\Models\Petshop\Quarto;
use App\Models\Petshop\SalaDeAula;
use App\Models\Petshop\Hotel;
use Carbon\Carbon;

class AgendamentoController extends Controller
{

    public function __construct()
    {
        // O projeto controla acesso via `ValidaAcesso` (permissão por URI no menu),
        // e não usa o middleware `permission` (Spatie).
    }

    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;

        $servicos = Servico::where('empresa_id', $empresaId)
            ->where('status', 1)
            ->get();

        $status_os = OrdemServico::statusOs();

        $data = Agendamento::where('empresa_id', $empresaId)
            ->with('itens', 'itens.servico')
            ->orderBy('data', 'desc')->get();

        $agendamentos1 = [];
        $agendamentos = [];

        if (__isSegmento(Auth::user()->empresa, 'PRO')) {
            $agendamentos_pro = OrdemServico::where('empresa_id', $empresaId)
                ->when(!empty($request->funcionario_id), function ($query) use ($request) {
                    return $query->where('funcionario_id', $request->funcionario_id);
                })
                ->when(!empty($request->estado), function ($query) use ($request) {
                    return $query->where('estado', $request->estado);
                })
                ->with(['cliente', 'servicos.servico', 'funcionario', 'itens.produto'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'estado' => $item->estado,
                        'estado_label' => $item->getStatusByValue($item->estado),
                        'codigo_sequencial' => $item->codigo_sequencial,
                        'modulo' => 'PRO',
                        'extendedProps' => [
                            'cliente_id' => $item->cliente->id ?? null,
                            'colaborador_id' => $item->funcionario_id ?? null,
                            'cliente' => $item->cliente->razao_social ?? '--',
                            'colaborador' => $item->funcionario->nome ?? ($item->servico->funcionario->nome ?? '--'),
                            'servicos' => $item->servicos
                                ->map(function($s){
                                    return [
                                        'id' => $s->id,
                                        'nome' => $s->nome,
                                        'categoria' => $s->categoria->nome ?? null,
                                        'valor' => $s->valor ?? 0,
                                        'tempo_execucao' => $s->tempo_execucao
                                    ];
                                }),
                            'produtos' => $item->itens->map(function($p){
                                    return [
                                        'id' => $p->id,
                                        'nome' => $p->nome,
                                        'categoria' => $p->categoria->nome ?? null,
                                        'valor_unitario' => $p->valor_unitario ?? 0,
                                        'pivot' => $p->pivot
                                    ];
                                }),
                            'horario' => __data_pt($item->data_inicio, false, 'Y/m/d H:i') . ' - ' . __data_pt($item->data_entrega, false, 'Y/m/d H:i'),
                            'data_entrada' => __data_pt($item->data_inicio, false, 'Y/m/d'),
                            'horario_entrada' => $item->data_inicio->format('H:i'),
                            'data_saida' => __data_pt($item->data_entrega, false, 'Y/m/d'),
                            'horario_saida' => Carbon::parse($item->data_entrega)->format('H:i'),
                            'descricao' => $item->descricao,
                        ],
                        'start' => Carbon::parse($item->data_inicio)->toIso8601String(),
                        'end'   => Carbon::parse($item->data_entrega)->toIso8601String(),
                        'className' => 'bg-primary',
                        'allDay' => false,
                    ];
                });

            $agendamentos = $agendamentos_pro;
        } else if (__isSegmento(Auth::user()->empresa, 'OFICINA')) {
            $agendamentos_oficina = OrdemServico::where('empresa_id', $empresaId)
                ->when(!empty($request->funcionario_id), function ($query) use ($request) {
                    return $query->where('funcionario_id', $request->funcionario_id);
                })
                ->when(!empty($request->estado), function ($query) use ($request) {
                    return $query->where('estado', $request->estado);
                })
                ->with(['cliente', 'servicos.servico', 'funcionario', 'itens.produto', 'veiculo'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'estado' => $item->estado,
                        'estado_label' => $item->getStatusByValue($item->estado),
                        'codigo_sequencial' => $item->codigo_sequencial,
                        'modulo' => 'OFICINA',
                        'extendedProps' => [
                            'cliente_id' => $item->cliente->id ?? null,
                            'colaborador_id' => $item->funcionario_id ?? null,
                            'cliente' => $item->cliente->razao_social ?? '--',
                            'colaborador' => $item->funcionario->nome ?? ($item->servico->funcionario->nome ?? '--'),
                            'servicos' => $item->servicos
                                ->map(function($s){
                                    return [
                                        'id' => $s->id,
                                        'nome' => $s->nome,
                                        'categoria' => $s->categoria->nome ?? null,
                                        'valor' => $s->valor ?? 0,
                                        'tempo_execucao' => $s->tempo_execucao
                                    ];
                                }),
                            'produtos' => $item->itens->map(function($p){
                                    return [
                                        'id' => $p->id,
                                        'nome' => $p->nome,
                                        'categoria' => $p->categoria->nome ?? null,
                                        'valor_unitario' => $p->valor_unitario ?? 0,
                                        'pivot' => $p->pivot
                                    ];
                                }),
                            'veiculo' => isset($item->veiculo->marca) ?
                            ($item->veiculo->marca->nome ?? '') . ' ' . $item->veiculo->modelo :
                            null,
                            'horario' => __data_pt($item->data_inicio, false, 'Y/m/d H:i') . ' - ' . __data_pt($item->data_entrega, false, 'Y/m/d H:i'),
                            'data_entrada' => __data_pt($item->data_inicio, false, 'Y/m/d'),
                            'horario_entrada' => $item->data_inicio->format('H:i'),
                            'data_saida' => __data_pt($item->data_entrega, false, 'Y/m/d'),
                            'horario_saida' => Carbon::parse($item->data_entrega)->format('H:i'),
                            'descricao' => $item->descricao,
                        ],
                        'start' => Carbon::parse($item->data_inicio)->toIso8601String(),
                        'end'   => Carbon::parse($item->data_entrega)->toIso8601String(),
                        'className' => 'bg-primary',
                        'allDay' => false,
                    ];
                });

            $agendamentos = $agendamentos_oficina;
        } else {
            foreach ($data as $item) {
                $primeiro_nome_servico = $item->itens->first()->servico->nome ?? null;
                $primeiro_nome_cliente = isset($item->cliente->razao_social) ? explode(' ', $item->cliente->razao_social)[0] : '';

                $agendamentos1[] = [
                    'title' => $primeiro_nome_cliente . ' | ' . $primeiro_nome_servico,
                    'start' => $item->data . " " . $item->inicio,
                    'end' => $item->data . " " . $item->termino,
                    'className' => $item->getPrioridade(),
                    'id' => $item->id
                ];
            }

            $agendamentos = $agendamentos1;
        }

        $funcionarios = Funcionario::where('empresa_id', $empresaId)
            ->select('id', 'nome')
            ->get();

        $quartos = Quarto::where('empresa_id', request()->empresa_id)->select('id', 'nome')->get()->map(function ($quarto) {
            $quarto->modulo = 'quarto';
            $quarto->nomec = 'Quarto:' . $quarto->nome;
            return $quarto;
        });

        $salasDeAula = SalaDeAula::where('empresa_id', request()->empresa_id)
            ->select('id', 'nome')
            ->get()
            ->map(function ($sala) {
                $sala->modulo = 'sala_de_aula';
                $sala->nomec = 'Sala_de_aula: ' . $sala->nome;
                return $sala;
            });

        // 3. Unir as coleções transformadas
        $salas = $quartos->merge($salasDeAula);

        $roles = Role::orderBy('name', 'desc')
            ->where('empresa_id', request()->empresa_id)
            ->get();

         $categorias = CategoriaServico::where(function ($q) {
            $q->where('empresa_id', request()->empresa_id);

            if (__isSegmento(Auth::user()->empresa, 'PETSHOP')) {
                $q->orWhereNull('empresa_id');
            }
        })->get();

        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();

        $segmento = null;

        if (Auth::check()) {
            $segmento = optional(
                Auth::user()->empresa?->empresa?->plano?->plano?->segmento
            )->nome;
        }

        return view('petshop.agendamento.index', compact(
            'agendamentos',
            'servicos',
            'clientes',
            'roles',
            'categorias',
            'segmento',
            'salas',
            'funcionarios',
            'status_os'
        ));
    }

    public function store(Request $request)
    {
        try {
            $agendamento = DB::transaction(function () use ($request) {
                $dataAgendamento = [
                    'funcionario_id' => $request->funcionario_id,
                    'cliente_id' => $request->cliente_id,
                    'veiculo_id' => $request->veiculo_id,
                    'data' => $request->data,
                    'inicio' => $request->inicio,
                    'termino' => $request->termino,
                    'prioridade' => $request->prioridade,
                    'observacao' => $request->observacao ?? "",
                    'total' => __convert_value_float($request->total),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'acrescimo' => $request->acrescimo ? $request->acrescimo : 0,
                    'empresa_id' => $request->empresa_id,
                    'animal_id' => $request->animal_id ?? null,
                ];

                $agendamento = Agendamento::create($dataAgendamento);

                foreach ($request->servicos as $index => $servico_id) {
                    $servico = Servico::findOrFail($servico_id);

                    $quantidade = $request->qtd_servicos[$index] ?? 1;

                    ItemAgendamento::create([
                        'agendamento_id' => $agendamento->id,
                        'servico_id' => $servico_id,
                        'quantidade' => $quantidade,
                        'valor' => $servico->valor,
                    ]);
                }

                return $agendamento;
            });
            __createLog($request->empresa_id, 'Agendamento', 'cadastrar', "Data: " . __data_pt($agendamento->data) . " - cliente: " . $agendamento->cliente->info);
            session()->flash("flash_success", "Agendamento cadastrado!");
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Agendamento', 'erro', $e->getMessage());
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->back();
    }

    public function show($id)
    {
        $item = Agendamento::with('veiculo')->findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('petshop.agendamento.show', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Agendamento::findOrFail($id);
        __validaObjetoEmpresa($item);
        $item->inicio = $request->inicio;
        $item->termino = $request->termino;
        $item->data = $request->data;
        $item->save();
        __createLog($request->empresa_id, 'Agendamento', 'editar', "Data: " . __data_pt($item->data) . " - cliente: " . $item->cliente->info);

        session()->flash("flash_success", "Agendamento alterado!");
        return redirect()->back();
    }

    public function updateStatus(Request $request, $id)
    {
        $item = Agendamento::findOrFail($id);
        __validaObjetoEmpresa($item);
        $item->status = 1;
        $item->save();
        session()->flash("flash_success", "Agendamento alterado!");
        return redirect()->route('petshop.agenda.index');
    }

    public function destroy($id)
    {
        $item = Agendamento::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {
            $descricaoLog = "Data: " . __data_pt($item->data) . " - cliente: " . $item->cliente->info;

            $item->itens()->delete();
            $item->delete();
            __createLog(request()->empresa_id, 'Agendamento', 'excluir', $descricaoLog);
            session()->flash("flash_success", "Agendamento removido!");
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Agendamento', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->route('petshop.agenda.index');
    }

    public function pdv($id)
    {
        $agendamento = Agendamento::findOrFail($id);
        __validaObjetoEmpresa($agendamento);

        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }

        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();

        $abertura = Caixa::where('empresa_id', request()->empresa_id)->where('usuario_id', get_id_user())
            ->where('status', 1)
            ->first();

        $config = Empresa::findOrFail(request()->empresa_id);
        if ($config == null) {
            session()->flash("flash_warning", "Configure antes de continuar!");
            return redirect()->route('config.index');
        }

        if ($config->natureza_id_pdv == null) {
            session()->flash("flash_warning", "Configure a natureza de operação padrão para continuar!");
            return redirect()->route('config.index');
        }

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $cliente = $agendamento->cliente;
        $funcionario = $agendamento->funcionario;
        $servicos = $agendamento->itens;
        $title = 'Finalizando agendamento #' . $agendamento->id;
        $caixa = __isCaixaAberto();

        $config = ConfigGeral::where('empresa_id', request()->empresa_id)->first();
        $tiposPagamento = Nfce::tiposPagamento();
        if ($config != null) {
            $config->tipos_pagamento_pdv = $config != null && $config->tipos_pagamento_pdv ? json_decode($config->tipos_pagamento_pdv) : [];
            $temp = [];
            if (sizeof($config->tipos_pagamento_pdv) > 0) {
                foreach ($tiposPagamento as $key => $t) {
                    if (in_array($t, $config->tipos_pagamento_pdv)) {
                        $temp[$key] = $t;
                    }
                }
                $tiposPagamento = $temp;
            }
        }

        $isVendaSuspensa = 0;

        return view(
            'front_box.create',
            compact(
                'categorias',
                'abertura',
                'funcionarios',
                'agendamento',
                'servicos',
                'title',
                'cliente',
                'funcionario',
                'caixa',
                'config',
                'tiposPagamento',
                'isVendaSuspensa'
            )
        );
    }
}
