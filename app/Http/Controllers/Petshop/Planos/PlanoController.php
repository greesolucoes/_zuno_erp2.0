<?php

namespace App\Http\Controllers\PetShop\Planos;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Plano;
use App\Services\Petshop\PlanoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Produto;
use App\Models\Servico;

class PlanoController extends Controller
{
    private PlanoService $planoService;

    public function __construct(PlanoService $planoService)
    {
        $this->planoService = $planoService;
    }

    public function index(Request $request)
    {
        $planos = $this->planoService->paginate($request->pesquisa);
        return view('petshop.planos.index', compact('planos'));
    }

    public function create()
    {
        $oldInput = session()->getOldInput();
        $planoData = !empty($oldInput) ? $oldInput : null;

        $servicos = $this->buildServicosFromOldInput($oldInput);
        $produtos = $this->buildProdutosFromOldInput($oldInput);

        return view('petshop.planos.create', compact('planoData', 'servicos', 'produtos'));
    }

    private function buildServicosFromOldInput(?array $oldInput)
    {
        $servicos = collect();
        if (empty($oldInput)) {
            return $servicos;
        }

        $servicoIds = collect($oldInput['servico_id'] ?? [])->filter();
        if ($servicoIds->isEmpty()) {
            return $servicos;
        }

        $servicoModels = Servico::whereIn('id', $servicoIds->all())->get()->keyBy('id');
        $copTipos = $oldInput['coparticipacao_tipo'] ?? [];
        $copValores = $oldInput['coparticipacao_valor'] ?? [];

        return $servicoIds->map(function ($id, $index) use ($servicoModels, $copTipos, $copValores) {
            $servico = $servicoModels->get($id);
            if (!$servico) {
                $servico = (object) [
                    'nome' => 'Serviço não encontrado',
                    'valor' => 0,
                    'tipo_servico_label' => '',
                ];
            }

            $item = new \stdClass();
            $item->servico_id = $id;
            $item->servico = $servico;
            $item->coparticipacao_tipo = $copTipos[$index] ?? null;
            $valorBruto = $copValores[$index] ?? null;
            $item->coparticipacao_valor = __convert_value_float($valorBruto);
            $item->coparticipacao_valor_display = $valorBruto;
            $item->subtotal = $servico->valor ?? 0;

            return $item;
        })->filter()->values();
    }

    private function buildProdutosFromOldInput(?array $oldInput)
    {
        $produtos = collect();
        if (empty($oldInput)) {
            return $produtos;
        }

        $produtoIds = collect($oldInput['produto_id'] ?? [])->filter();
        if ($produtoIds->isEmpty()) {
            return $produtos;
        }

        $produtoModels = Produto::whereIn('id', $produtoIds->all())->get()->keyBy('id');
        $quantidades = $oldInput['qtd_produto'] ?? [];
        $variacoes = $oldInput['variacao_id'] ?? [];

        return $produtoIds->map(function ($id, $index) use ($produtoModels, $quantidades, $variacoes) {
            $produto = $produtoModels->get($id);
            if (!$produto) {
                $produto = (object) [
                    'nome' => 'Produto não encontrado',
                    'valor_unitario' => 0,
                ];
            }

            $quantidadeBruta = $quantidades[$index] ?? null;
            $quantidade = __convert_value_int($quantidadeBruta);

            $item = new \stdClass();
            $item->produto_id = $id;
            $item->produto = $produto;
            $item->variacao_id = $variacoes[$index] ?? null;
            $item->quantidade = $quantidade;
            $item->quantidade_display = $quantidadeBruta;
            $valorUnitario = $produto->valor_unitario ?? 0;
            $item->valor = $valorUnitario;
            $item->subtotal = $valorUnitario * ($quantidade ?? 0);

            return $item;
        })->filter()->values();
    }

    public function store(Request $request)
    {
        $request->merge([
            'preco_plano' => __convert_value_float($request->preco_plano),
            'multa_noshow_valor' => __convert_value_float($request->multa_noshow_valor),
            'dias_tolerancia_atraso' => __convert_value_int($request->dias_tolerancia_atraso),
        ]);

        $validated = $request->validate([
            'slug' => 'required|string|max:100',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'periodo' => ['required', Rule::in(['dia','semana','mes','ano'])],
            'frequencia_tipo' => ['required', Rule::in(['ilimitado','limitado'])],
            'frequencia_qtd' => ['required_if:frequencia_tipo,limitado', 'integer', 'min:1', 'prohibited_if:frequencia_tipo,ilimitado'],
            'preco_plano' => ['required','numeric'],
            'multa_noshow_tipo' => ['required', Rule::in(['percentual','valor_fixo'])],
            'multa_noshow_valor',
            'bloquear_por_inadimplencia' => ['required', Rule::in(['sim','nao'])],
            'dias_tolerancia_atraso' => ['nullable','integer','min:0'],
        ], [
            'periodo.required' => 'Selecione um período válido.',
            'periodo.in' => 'Selecione um período válido.',
            'frequencia_tipo.required' => 'Escolha Ilimitado ou Limitado.',
            'frequencia_tipo.in' => 'Escolha Ilimitado ou Limitado.',
            'frequencia_qtd.required_if' => 'Informe a quantidade.',
            'frequencia_qtd.integer' => 'Informe um número ≥ 1.',
            'frequencia_qtd.min' => 'Informe um número ≥ 1.',
            'frequencia_qtd.prohibited_if' => 'Não informe quantidade quando a frequência for ilimitada.',
        ]);

        // Monta estrutura de versões com serviços e produtos
        $servicos = [];
        foreach ($request->servico_id ?? [] as $i => $id) {
            if (!$id) {
                continue;
            }
            $servicos[] = [
                'servico_id' => $id,
                'qtd_por_ciclo' => __convert_value_int($request->qtd_servico[$i] ?? 1),
                'valor_servico' => __convert_value_float($request->subtotal_servico[$i] ?? null),
                'coparticipacao_tipo' => $request->coparticipacao_tipo[$i] ?? null,
                'coparticipacao_valor' => __convert_value_float($request->coparticipacao_valor[$i] ?? null),
            ];
        }

        $produtos = [];
        foreach ($request->produto_id ?? [] as $i => $id) {
            if (!$id) {
                continue;
            }
            $produtos[] = [
                'produto_id' => $id,
                'variacao_id' => $request->variacao_id[$i] ?? null,
                'qtd_por_ciclo' => __convert_value_int($request->qtd_produto[$i] ?? 1),
            ];
        }

        $versoes = [];
        if ($request->input('versoes.0.vigente_desde')) {
            $versoes[] = [
                'vigente_desde' => $request->input('versoes.0.vigente_desde'),
                'vigente_ate' => $request->input('versoes.0.vigente_ate'),
                'servicos' => $servicos,
                'produtos' => $produtos,
            ];
        }

        $data = array_merge($validated, [
            'empresa_id' => request()->empresa_id,
            'filial_id' => optional(__getLocalAtivo())->id,
            'versoes' => $versoes,
        ]);

        $this->planoService->create($data);
        session()->flash('flash_success', 'Plano criado com sucesso!');

        return redirect()->route('petshop.gerenciar.planos');
    }

    public function edit(Plano $plano)
    {
        $plano->load(['versoes.servicos.servico', 'versoes.produtos.produto']);
        $versao = $plano->versoes->first();
        $planoData = $plano->toArray();
        if ($versao) {
            $planoData['versoes'][0] = $versao->toArray();
            $planoData['versoes'][0]['vigente_desde'] = $versao->vigente_desde
                ? Carbon::parse($versao->vigente_desde)->format('Y-m-d')
                : null;
            $planoData['versoes'][0]['vigente_ate'] = $versao->vigente_ate
                ? Carbon::parse($versao->vigente_ate)->format('Y-m-d')
                : null;
        }
        $servicos = $versao ? $versao->servicos : collect();
        $produtos = $versao ? $versao->produtos : collect();

        $produtos->each(function ($item) {
            $valor = $item->produto->valor_unitario ?? 0;
            $item->setAttribute('valor', $valor);
            $item->setAttribute('subtotal', $valor * $item->quantidade);
        });

        $frete = $servicos->first(function ($item) {
            return $item->servico->categoria && $item->servico->categoria->nome === 'FRETE';
        });

        $servicos = $servicos
            ->filter(function ($item) {
                return $item->servico->categoria && strtoupper($item->servico->categoria->nome) !== 'FRETE';
            })
        ->values();

        return view('petshop.planos.edit', compact('plano', 'planoData', 'servicos', 'frete', 'produtos'));
    }

    public function update(Request $request, Plano $plano)
    {

         $request->merge([
            'preco_plano' => __convert_value_float($request->preco_plano),
            'multa_noshow_valor' => __convert_value_float($request->multa_noshow_valor),
            'dias_tolerancia_atraso' => __convert_value_int($request->dias_tolerancia_atraso),
        ]);

        $validated = $request->validate([
            'slug' => 'required|string|max:100',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'periodo' => ['required', Rule::in(['dia','semana','mes','ano'])],
            'frequencia_tipo' => ['required', Rule::in(['ilimitado','limitado'])],
            'frequencia_qtd' => ['required_if:frequencia_tipo,limitado', 'integer', 'min:1', 'prohibited_if:frequencia_tipo,ilimitado'],
            'preco_plano' => ['required','numeric'],
            'multa_noshow_tipo' => ['required', Rule::in(['percentual','valor_fixo'])],
            'multa_noshow_valor',
            'bloquear_por_inadimplencia' => ['required', Rule::in(['sim','nao'])],
            'dias_tolerancia_atraso' => ['nullable','integer','min:0'],
        ], [
            'periodo.required' => 'Selecione um período válido.',
            'periodo.in' => 'Selecione um período válido.',
            'frequencia_tipo.required' => 'Escolha Ilimitado ou Limitado.',
            'frequencia_tipo.in' => 'Escolha Ilimitado ou Limitado.',
            'frequencia_qtd.required_if' => 'Informe a quantidade.',
            'frequencia_qtd.integer' => 'Informe um número ≥ 1.',
            'frequencia_qtd.min' => 'Informe um número ≥ 1.',
            'frequencia_qtd.prohibited_if' => 'Não informe quantidade quando a frequência for ilimitada.',
        ]);

        // Monta estrutura de versões com serviços e produtos
        $servicos = [];
        foreach ($request->servico_id ?? [] as $i => $id) {
            if (!$id) {
                continue;
            }
            $servicos[] = [
                'servico_id' => $id,
                'qtd_por_ciclo' => __convert_value_int($request->qtd_servico[$i] ?? 1),
                'valor_servico' => __convert_value_float($request->subtotal_servico[$i] ?? null),
                'coparticipacao_tipo' => $request->coparticipacao_tipo[$i] ?? null,
                'coparticipacao_valor' => __convert_value_float($request->coparticipacao_valor[$i] ?? null),
            ];
        }

        $produtos = [];
        foreach ($request->produto_id ?? [] as $i => $id) {
            if (!$id) {
                continue;
            }
            $produtos[] = [
                'produto_id' => $id,
                'variacao_id' => $request->variacao_id[$i] ?? null,
                'qtd_por_ciclo' => __convert_value_int($request->qtd_produto[$i] ?? 1),
            ];
        }

        $versoes = [];
        if ($request->input('versoes.0.vigente_desde')) {
            $versoes[] = [
                'vigente_desde' => $request->input('versoes.0.vigente_desde'),
                'vigente_ate' => $request->input('versoes.0.vigente_ate'),
                'servicos' => $servicos,
                'produtos' => $produtos,
            ];
        }

        $data = array_merge($validated, [
            'empresa_id' => request()->empresa_id,
            'filial_id' => optional(__getLocalAtivo())->id,
            'versoes' => $versoes,
        ]);

        $this->planoService->update($plano, $data);
        session()->flash('flash_success', 'Plano atualizado com sucesso!');

        return redirect()->route('petshop.gerenciar.planos');
    }

    public function destroy(Plano $plano)
    {
        $this->planoService->delete($plano);
        session()->flash('flash_success', 'Plano removido com sucesso!');

        return redirect()->route('petshop.gerenciar.planos');
    }
}
