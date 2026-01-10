<?php

namespace App\Http\Controllers\Petshop\Estetica;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\Petshop\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Petshop\Estetica;
use App\Models\Servico;
use App\Models\Produto;
use App\Models\Petshop\EsteticaServico;
use App\Models\Petshop\EsteticaProduto;
use App\Models\ServicoOs;
use App\Models\ProdutoOs;
use App\Models\Petshop\Hotel;
use App\Models\Cliente;
use App\Models\Plano;
use App\Models\Role;
use App\Models\CategoriaServico;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Petshop\Quarto;
use App\Models\Petshop\SalaDeAula;
use App\Models\PlanoUser;
use App\Services\Notificacao\EsteticaNotificacaoService;
use App\Services\Petshop\EsteticaService;
use App\Services\Petshop\PlanoLimiteService;
use Dompdf\Dompdf;

class EsteticaController extends Controller
{
    protected EsteticaService $estetica_service;

    public function __construct(private PlanoLimiteService $limiteService, EsteticaService $estetica_service)
    {
        $this->estetica_service = $estetica_service;
    }

    public function index(Request $request)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;

        $pesquisa = $request->input('pesquisa');
        $data = Estetica::with(['animal', 'cliente', 'servicos.servico', 'produtos', 'ordemServico'])
            ->where('empresa_id', $empresa_id)
            ->whereIn('estado', ['agendado', 'em_andamento', 'concluido'])
            ->when($pesquisa, function ($q) use ($pesquisa) {
                $q->whereHas('animal', fn($q) => $q->where('nome', 'like', "%{$pesquisa}%"))
                    ->orWhereHas('cliente', fn($q) => $q->where('nome_fantasia', 'like', "%{$pesquisa}%"));
            })
            ->orderByDesc('created_at')
            ->paginate(env("PAGINACAO"))
            ->appends($request->all());

        return view('esteticas.index', compact('data'));
    }

    public function create()
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;

        $servicos = Servico::where('empresa_id', $empresa_id)->whereHas('categoria', function ($query) {
            $query->where('nome', 'ESTETICA');
        })->get();

        $servicosFormatados = $servicos->mapWithKeys(fn($s) => [
            $s->id => $s->nome . ' (R$ ' . number_format($s->valor, 2, ',', '.') . ')'
        ]);

        $status_estetica = Estetica::statusEstetica();

        return view('esteticas.create', compact('servicos', 'servicosFormatados', 'status_estetica'));
    }

    public function store(Request $request)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;
        if ($request->filled('data_agendamento')) {
            try {
                $request->merge([
                    'data_agendamento' => Carbon::parse($request->data_agendamento)->format('Y-m-d')
                ]);
            } catch (\Exception $e) {
                Log::warning('Formato de data_agendamento inválido', ['value' => $request->data_agendamento]);
            }
        }

        if ($request->filled('horario_agendamento') && $request->filled('horario_saida')) {
            try {
                $request->merge([
                    'horario_agendamento' => Carbon::parse($request->horario_agendamento)->format('H:i'),
                    'horario_saida' => Carbon::parse($request->horario_agendamento)->addMinutes(30)->format('H:i')
                ]);
            } catch (\Exception $e) {
                Log::warning('Formato de horario_agendamento inválido', ['value' => $request->horario_agendamento]);
            }
        }

        $request->validate([
            'animal_id'              => 'required|exists:petshop_animais,id',
            'colaborador_id'      => 'nullable|exists:funcionarios,id',
            'servico_id'          => 'required|array|min:1',
            'servico_id.*'        => 'exists:servicos,id',
            'produto_id'          => 'nullable|array',
            'produto_id.*'        => 'nullable|exists:produtos,id',
            'qtd_produto'         => 'nullable|array',
            'data_agendamento'    => 'required|date_format:Y-m-d',
            'horario_agendamento' => 'required|date_format:H:i',
            'horario_saida'       => 'required|date_format:H:i',   
            'descricao'           => 'nullable|string|max:1000',
        ]);

        try {
            $pet = Animal::findOrFail($request->animal_id);

            $estetica = Estetica::create([
                'empresa_id'         => $empresa_id,
                'animal_id'          => $pet->id,
                'cliente_id'         => $pet->cliente_id,
                'colaborador_id'     => $request->colaborador_id ?: null,
                'plano_id'           => $request->plano_id,
                'descricao'          => $request->descricao,
                'data_agendamento'   => $request->data_agendamento,
                'horario_agendamento'=> $request->horario_agendamento,
                'horario_saida'      => $request->horario_saida,
                'estado'             => $request->estado ?? 'agendado',
            ]);

            foreach ($request->servico_id as $index => $servicoId) {
                $servico = Servico::findOrFail($servicoId);
                EsteticaServico::create([
                    'estetica_id' => $estetica->id,
                    'servico_id'  => $servico->id,
                    'subtotal'    => __convert_value_bd($request->subtotal_servico[$index]) ?? 0,
                ]);
            }

            $servico_frete = $estetica->servicos->filter(function ($item) {
                return $item->servico->categoria && $item->servico->categoria->nome === 'FRETE';
            });

            if ($servico_frete->first()) {
                $endereco_cliente_data = [
                    'cep' => $request->cep,
                    'rua' => $request->rua,
                    'bairro' => $request->bairro,
                    'numero' => $request->numero,
                    'complemento' => $request->complemento,

                    'cidade_id' => $request->modal_cidade_id,
                    'estetica_id' => $estetica->id,
                    'cliente_id' => $estetica->cliente_id,
                ];

                $this->estetica_service->updateOrCreateEsteticaClienteEndereco($estetica->id, $endereco_cliente_data);
            }

            if ($request->filled('produto_id')) {
                foreach ($request->produto_id as $index => $produtoId) {
                    if (!$produtoId) {
                        continue;
                    }
                    $produto = Produto::findOrFail($produtoId);
                    $qtd = intval($request->qtd_produto[$index] ?? 1);
                    $subtotal = ($produto->valor_unitario ?? 0) * $qtd;

                    EsteticaProduto::create([
                        'estetica_id' => $estetica->id,
                        'produto_id'  => $produto->id,
                        'quantidade'  => $qtd,
                        'valor'       => $produto->valor_unitario ?? 0,
                        'subtotal'    => $subtotal,
                    ]);
                }
            }

            if ($estetica->estado !== 'pendente_aprovacao') {
                if (!$this->estetica_service->criarOrdemServico($estetica)) {
                    session()->flash('flash_error', 'Limite de uso do serviço atingido para este período.');
                    return redirect()->back()->withInput();
                }
            }

            $esteticaParaNotificacao = $estetica->fresh(['empresa', 'cliente', 'animal', 'servicos.servico']);
            (new EsteticaNotificacaoService())->nova($esteticaParaNotificacao ?? $estetica);

            session()->flash('flash_success', 'Agendamento criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar agendamento de estética', ['exception' => $e]);
            session()->flash('flash_error', 'Erro ao salvar agendamento: ' . $e->getMessage());
        }

        if ($request->back == 1) {
            return redirect()->back()->with('flash_success', 'Agendamento criado com sucesso!');
        }

        return redirect()->route('esteticas.index')->with('flash_success', 'Agendamento criado com sucesso!');
    }

    public function edit($id)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;

        $data = Estetica::where('empresa_id', $empresa_id)->findOrFail($id);

        $servicos = Servico::where('empresa_id', $empresa_id)->whereHas('categoria', function ($query) {
            $query->where('nome', 'ESTETICA');
        })->get();

        $frete = $data->servicos->first(function ($servico) {
            return $servico->servico->categoria && $servico->servico->categoria->nome === 'FRETE';
        });

        $servicos_estetica =  $data->servicos->filter(function ($item) {
            return !$item->servico->categoria || $item->servico->categoria->nome === 'ESTETICA';
        });

        $data->setRelation('servicos', $servicos_estetica);

        $servicosFormatados = $servicos->mapWithKeys(fn($s) => [
            $s->id => $s->nome . ' (R$ ' . number_format($s->valor, 2, ',', '.') . ')'
        ]);

        $status_estetica = Estetica::statusEstetica();

        $data->esteticaClienteEndereco && $data->esteticaClienteEndereco = $data->esteticaClienteEndereco->load('cidade');

        $data->data_agendamento = $data->data_agendamento ? $data->data_agendamento->format('Y-m-d') : null;

        return view('esteticas.edit', compact('data', 'frete', 'servicos', 'servicosFormatados', 'status_estetica'));
    }



    public function update(Request $request, $id)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;
        if ($request->filled('data_agendamento')) {
            try {
                $request->merge([
                    'data_agendamento' => Carbon::parse($request->data_agendamento)->format('Y-m-d')
                ]);
            } catch (\Exception $e) {
                Log::warning('Formato de data_agendamento inválido', ['value' => $request->data_agendamento]);
            }
        }

        if ($request->filled('horario_agendamento') && $request->filled('horario_saida')) {
            try {
                $request->merge([
                    'horario_agendamento' => Carbon::parse($request->horario_agendamento)->format('H:i'),
                    'horario_saida' => Carbon::parse($request->horario_saida)->format('H:i')
                ]);
            } catch (\Exception $e) {
                Log::warning('Formato de horario_agendamento inválido', ['value' => $request->horario_agendamento]);
            }
        }

        $validator = Validator::make($request->all(), [
            'animal_id'              => 'required|exists:petshop_animais,id',
            'colaborador_id'      => 'nullable|exists:funcionarios,id',
            'servico_id'          => 'required|array|min:1',
            'servico_id.*'        => 'exists:servicos,id',
            'produto_id'          => 'nullable|array',
            'produto_id.*'        => 'nullable|exists:produtos,id',
            'qtd_produto'         => 'nullable|array',
            'data_agendamento'    => 'required|date_format:Y-m-d',
            'horario_agendamento' => 'required|date_format:H:i',
            'horario_saida'       => 'required|date_format:H:i',
            'descricao'           => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            Log::warning('Falha de validação ao atualizar agendamento de estética', [
                'estetica_id' => $id,
                'errors'      => $validator->errors()->toArray(),
                'payload'     => $request->all(),
            ]);

            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $estetica = Estetica::where('empresa_id', $empresa_id)->findOrFail($id);
            $pet = Animal::findOrFail($request->animal_id);

            $estetica->update([
                'animal_id'          => $pet->id,
                'cliente_id'         => $pet->cliente_id,
                'colaborador_id'     => $request->colaborador_id ?: null,
                'plano_id'           => $request->plano_id,
                'descricao'          => $request->descricao,
                'data_agendamento'   => $request->data_agendamento,
                'horario_agendamento'=> $request->horario_agendamento,
                'horario_saida'      => $request->horario_saida,
                'estado'             => $request->estado ?? $estetica->estado,
            ]);

            $estetica->servicos()->delete();
            $valorTotal = 0;
            $tempo_execucao_servicos = 0;

            foreach ($request->servico_id as $index => $servicoId) {
                $servico = Servico::findOrFail($servicoId);
                EsteticaServico::create([
                    'estetica_id' => $estetica->id,
                    'servico_id'  => $servico->id,
                    'subtotal'    => __convert_value_bd($request->subtotal_servico[$index]) ?? 0,
                ]);
                $valorTotal += __convert_value_bd($request->subtotal_servico[$index]) ?? 0;
                $tempo_execucao_servicos += $servico->tempo_execucao ?? 0;
            }

            $servico_frete = $estetica->servicos->filter(function ($item) {
                return $item->servico->categoria && $item->servico->categoria->nome === 'FRETE';
            });

            if ($servico_frete->first()) {
                $endereco_cliente_data = [
                    'cep' => $request->cep,
                    'rua' => $request->rua,
                    'bairro' => $request->bairro,
                    'numero' => $request->numero,
                    'complemento' => $request->complemento,

                    'cidade_id' => $request->modal_cidade_id,
                    'estetica_id' => $estetica->id,
                    'cliente_id' => $estetica->cliente_id,
                ];

                $this->estetica_service->updateOrCreateEsteticaClienteEndereco($estetica->id, $endereco_cliente_data);
            } else {
                if (isset($estetica->esteticaClienteEndereco)) {
                    $estetica->esteticaClienteEndereco->delete();
                }
            }

            $estetica->produtos()->delete();
            if ($request->filled('produto_id')) {
                foreach ($request->produto_id as $index => $produtoId) {
                    if (!$produtoId) {
                        continue;
                    }
                    $produto = Produto::findOrFail($produtoId);
                    $qtd = intval($request->qtd_produto[$index] ?? 1);
                    $subtotal = ($produto->valor_unitario ?? 0) * $qtd;
                    EsteticaProduto::create([
                        'estetica_id' => $estetica->id,
                        'produto_id'  => $produto->id,
                        'quantidade'  => $qtd,
                        'valor'       => $produto->valor_unitario ?? 0,
                        'subtotal'    => $subtotal,
                    ]);
                    $valorTotal += $subtotal;
                }
            }

            $data = Carbon::parse($request->data_agendamento)
                ->setTimeFromTimeString($request->horario_agendamento);
            $data_final_agendamento = $data->copy()->addMinutes($tempo_execucao_servicos);

            $this->estetica_service->updateValorTotal($estetica->id);
            $this->estetica_service->updateContaReceberDataVencimento($estetica->id);

            $ordem = $estetica->ordemServico;
            if ($ordem) {
                $ordem->update([
                    'cliente_id'         => $pet->cliente_id,
                    'empresa_id'         => $empresa_id,
                    'funcionario_id'     => $request->colaborador_id,
                    'animal_id'          => $pet->id,
                    'valor'              => $valorTotal,
                    'total_sem_desconto' => $valorTotal,
                    'data_inicio'        => $data,
                    'data_entrega'       => $data_final_agendamento,
                ]);
            } else {
                $codigoSequencial = (OrdemServico::where('empresa_id', $empresa_id)->max('codigo_sequencial') ?? 0) + 1;
                $ordem = OrdemServico::create([
                    'descricao'          => 'Ordem de Serviço Estetica',
                    'cliente_id'         => $pet->cliente_id,
                    'empresa_id'         => $empresa_id,
                    'funcionario_id'     => $request->colaborador_id,
                    'animal_id'          => $pet->id,
                    'plano_id'           => null,
                    'modulos'            => 'Estetica',
                    'modulo_ids'         => ['Estetica' => [$estetica->id]],
                    'usuario_id'         => auth()->id(),
                    'codigo_sequencial'  => $codigoSequencial,
                    'valor'              => $valorTotal,
                    'total_sem_desconto' => $valorTotal,
                    'data_inicio'        => $data,
                    'data_entrega'       => $data,
                    'estado'             => 'AF',
                ]);
                $estetica->update(['ordem_servico_id' => $ordem->id]);
            }

            ServicoOs::where('ordem_servico_id', $ordem->id)->delete();
            ProdutoOs::where('ordem_servico_id', $ordem->id)->delete();
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
                ProdutoOs::create([
                    'ordem_servico_id' => $ordem->id,
                    'produto_id'       => $produto->produto_id,
                    'quantidade'       => $produto->quantidade,
                    'valor'            => $produto->valor,
                    'subtotal'         => $produto->subtotal,
                ]);
            }

            session()->flash('flash_success', 'Agendamento atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Erro ao atualizar agendamento: ' . $e->getMessage());
        }

        if ($request->back == 1) {
            return redirect()->back();
        }

        return redirect()->route('esteticas.index');
    }

    public function printEnderecoEntrega($id){
        $estetica = Estetica::findOrFail($id);
        
        $item = $estetica->esteticaClienteEndereco;

        $height = 350;
        
        $config = Empresa::where('id', $item->empresa_id)->first();

        $p = view('esteticas.cupom_entrega', compact('config', 'item', 'estetica'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $domPdf->setPaper([0, 0, 220, $height]);
        $domPdf->render();  

        $domPdf->stream("Endereço de entrega.pdf", array("Attachment" => false));
    }


    public function destroy($id)
    {
        try {
            $empresa_id = Auth::user()?->empresa?->empresa_id;
            $estetica = Estetica::where('empresa_id', $empresa_id)->findOrFail($id);
            $planoId = $estetica->plano_id;
            $dataEstetica = Carbon::parse($estetica->entrada);
            $mesEstetica = $dataEstetica->format('Y-m');

            $ordem = $estetica->ordemServico;

            $estetica->delete();

            if ($ordem) {
                $moduloIds = is_string($ordem->modulo_ids)
                    ? json_decode($ordem->modulo_ids, true)
                    : $ordem->modulo_ids;

                if (isset($moduloIds['Estetica'])) {
                    $moduloIds['Estetica'] = array_filter(
                        $moduloIds['Estetica'],
                        fn($i) => (int) $i !== (int) $id
                    );

                    if (empty($moduloIds['Estetica'])) {
                        unset($moduloIds['Estetica']);
                    }
                }

                if (empty($moduloIds)) {
                    ServicoOs::where('ordem_servico_id', $ordem->id)->delete();
                    $ordem->delete();
                } else {
                    $ordem->modulo_ids = $moduloIds;

                    $mesOrdem = Carbon::parse($ordem->data_entrega)->format('Y-m');
                    if ($mesOrdem === $mesEstetica) {
                        $inicioMes = Carbon::parse($mesOrdem . '-01')->startOfMonth();
                        $fimMes = Carbon::parse($mesOrdem . '-01')->endOfMonth();

                        $valorHotel = Hotel::where('empresa_id', $empresa_id)
                            ->where('plano_id', $planoId)
                            ->whereBetween('checkin', [$inicioMes, $fimMes])
                            ->sum('valor');

                        $valorEstetica = Estetica::where('empresa_id', $empresa_id)
                            ->where('plano_id', $planoId)
                            ->whereBetween('entrada', [$inicioMes, $fimMes])
                            ->sum('valor');

                        $ordem->valor = $valorHotel + $valorEstetica;
                    }

                    $ordem->save();
                }
            }

            $plano = Plano::find($planoId);
            if ($plano) {
                $totalHotel = Hotel::where('plano_id', $planoId)
                    ->where('empresa_id', $empresa_id)
                    ->sum('valor');

                $totalEstetica = Estetica::where('plano_id', $planoId)
                    ->where('empresa_id', $empresa_id)
                    ->sum('valor');

                $plano->total = $totalHotel + $totalEstetica;
                $plano->save();

                if ($plano->total == 0) {
                    $plano->servicos()->delete();
                    $plano->delete();
                }
            }

            session()->flash('flash_success', 'Agendamento excluído e plano atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Erro ao excluir agendamento: ' . $e->getMessage());
        }

        return redirect()->route('esteticas.index');
    }

    public function pendentes(Request $request)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;

        $pesquisa = $request->input('pesquisa');
        $data = Estetica::with(['animal', 'cliente', 'servicos.servico', 'produtos', 'ordemServico'])
            ->where('empresa_id', $empresa_id)
            ->whereNotNull('plano_id')
            ->where('estado', 'pendente_aprovacao')
            ->whereHas('servicos.servico.categoria', function ($q) {
                $q->where('nome', 'ESTETICA');
            })
            ->when($pesquisa, function ($q) use ($pesquisa) {
                $q->whereHas('animal', fn($q) => $q->where('nome', 'like', "%{$pesquisa}%"))
                    ->orWhereHas('cliente', fn($q) => $q->where('nome_fantasia', 'like', "%{$pesquisa}%"));
            })
            ->orderByDesc('created_at')
            ->paginate(env("PAGINACAO"))
            ->appends($request->all());

        return view('esteticas.agendamento.pendente', compact('data'));
    }

    public function pendentesAvulso(Request $request)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;

        $pesquisa = $request->input('pesquisa');
        $data = Estetica::with(['animal', 'cliente', 'servicos.servico', 'produtos', 'ordemServico'])
            ->where('empresa_id', $empresa_id)
            ->whereNull('plano_id')
            ->where('estado', 'pendente_aprovacao')
            ->whereHas('servicos.servico.categoria', function ($q) {
                $q->where('nome', 'ESTETICA');
            })
            ->when($pesquisa, function ($q) use ($pesquisa) {
                $q->whereHas('animal', fn($q) => $q->where('nome', 'like', "%{$pesquisa}%"))
                    ->orWhereHas('cliente', fn($q) => $q->where('nome_fantasia', 'like', "%{$pesquisa}%"));
            })
            ->orderByDesc('created_at')
            ->paginate(env("PAGINACAO"))
            ->appends($request->all());

        return view('esteticas.agendamento.pendente_avulso', compact('data'));
    }

    public function aprovar(Estetica $estetica)
    {
        $res = $this->estetica_service->aprovar($estetica);

        if (!$res['success']) {
            session()->flash('flash_error', $res['message']);

            return redirect()->back();
        } else {
            session()->flash('flash_success', $res['message']);
        }

        $route = $estetica->plano_id
            ? 'petshop.esteticista.agendamentos.pendente'
            : 'petshop.esteticista.agendamentos.pendente-avulso';

        return redirect()->route($route);
    }

    public function rejeitar(Estetica $estetica)
    {
        $res = $this->estetica_service->rejeitar($estetica);

        if (!$res['success']) {
            session()->flash('flash_error', 'Ocorreu um erro desconhecido ao rejeitar o agendamento.');

            return redirect()->back();
        } else {
            session()->flash('flash_success', $res['message']);
        }

        $route = $estetica->plano_id
            ? 'petshop.esteticista.agendamentos.pendente'
            : 'petshop.esteticista.agendamentos.pendente-avulso';

        return redirect()->route($route);
    }

    public function agendstore(Request $request)
    {
        try {
            // Mapeia os campos do request de agendamento para os nomes esperados no store()
            $novoRequest = new Request([
                'animal_id'              => $request->animal_id,
                'servico_id'          => $request->servico_id ?? ($request->servico ? [$request->servico] : []),
                'colaborador_id'      => $request->colaborador_id ?? $request->funcionario ?? $request->funcionario_id,
                'produto_id'          => $request->produto_id,
                'qtd_produto'         => $request->qtd_produto,
                'data_agendamento'    => $request->data_agendamento ?? $request->data ?? $request->dataatual,
                'horario_agendamento' => $request->horario_agendamento ?? $request->horario_inicio,
                'descricao'           => $request->descricao ?? $request->observacao,
                'plano_id'            => null,
                'estado'              => $request->estado ?? 'agendado'
            ]);

            // Mescla os dados simulados com o original para manter _token etc.
            $novoRequest->merge([
                '_token' => $request->_token,
                'back' => 1
            ]);

            // Atualiza quando existir ID, senão cria novo
            if ($request->agendamento_id) {
                return $this->update($novoRequest, $request->agendamento_id);
            }

            // Chama o mesmo método usado no store
            return $this->store($novoRequest);
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Erro ao processar agendamento: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function show()
    {
        return redirect()->route('estetica.esteticas.agend');
    }
}
