<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ItemVendaCaixa;
use App\Models\Produto;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\VendaCaixaPreVenda;
use App\Models\VendaCaixa;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrenteCaixaController extends Controller
{
    public function fluxoDiario(Request $request)
    {
        $empresaId = (int)($request->empresa_id ?? 0);
        $usuarioId = (int)($request->usuario_id ?? 0);
        if ($empresaId <= 0) {
            return response()->json(['message' => 'empresa_id é obrigatório'], 400);
        }
        if ($usuarioId <= 0) {
            return response()->json(['message' => 'usuario_id é obrigatório'], 400);
        }

        $abertura = \App\Models\AberturaCaixa::where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->first();

        if (!$abertura) {
            return response()->json([
                'abertura' => null,
                'sangrias' => [],
                'suprimentos' => [],
                'vendas' => [],
            ], 200);
        }

        $inicio = $abertura->created_at;
        $fim = Carbon::now();

        $sangrias = SangriaCaixa::where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->whereBetween('created_at', [$inicio, $fim])
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'valor' => __moeda($s->valor),
                'created_at' => __data_pt($s->created_at, 1),
            ])
            ->values();

        $suprimentos = SuprimentoCaixa::where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->whereBetween('created_at', [$inicio, $fim])
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'valor' => __moeda($s->valor),
                'created_at' => __data_pt($s->created_at, 1),
            ])
            ->values();

        $vendas = VendaCaixa::where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->whereBetween('created_at', [$inicio, $fim])
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'created_at' => __data_pt($v->created_at, 1),
                'valor_total' => __moeda($v->valor_total),
                'tipo_pagamento' => VendaCaixa::getTipoPagamento($v->tipo_pagamento),
            ])
            ->values();

        return response()->json([
            'abertura' => [
                'id' => $abertura->id,
                'valor' => __moeda($abertura->valor),
                'created_at' => __data_pt($abertura->created_at, 1),
            ],
            'sangrias' => $sangrias,
            'suprimentos' => $suprimentos,
            'vendas' => $vendas,
        ], 200);
    }

    public function preVendas(Request $request)
    {
        $empresaId = (int)($request->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return response()->json(['message' => 'empresa_id é obrigatório'], 400);
        }

        $items = VendaCaixaPreVenda::with('vendedor')
            ->where('empresa_id', $empresaId)
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(fn($pv) => [
                'id' => $pv->id,
                'vendedor_nome' => $pv->vendedor->nome ?? '--',
                'valor_total' => __moeda($pv->valor_total),
                'created_at' => __data_pt($pv->created_at, 1),
                'observacao' => $pv->observacao ?? '',
            ])
            ->values();

        return response()->json($items, 200);
    }

    public function preVendaShow(Request $request, int $id)
    {
        $empresaId = (int)($request->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return response()->json(['message' => 'empresa_id é obrigatório'], 400);
        }

        $pv = VendaCaixaPreVenda::with(['itens.produto', 'cliente'])
            ->where('empresa_id', $empresaId)
            ->where('status', 0)
            ->findOrFail($id);

        return response()->json([
            'id' => $pv->id,
            'cliente' => $pv->cliente ? $pv->cliente->toArray() : null,
            'itens' => $pv->itens->map(fn($i) => [
                'produto_id' => $i->produto_id,
                'quantidade' => $i->quantidade,
                'valor' => $i->valor,
                'produto' => $i->produto ? $i->produto->toArray() : null,
            ])->values(),
        ], 200);
    }

    public function suspender(Request $request)
    {
        try {
            $empresaId = (int)($request->empresa_id ?? 0);
            $usuarioId = (int)($request->usuario_id ?? 0);
            if ($empresaId <= 0) {
                return response()->json(['message' => 'empresa_id é obrigatório'], 400);
            }
            if ($usuarioId <= 0) {
                return response()->json(['message' => 'usuario_id é obrigatório'], 400);
            }

            $produtoIds = $request->input('produto_id') ?: $request->input('produto_id[]') ?: [];
            $quantidades = $request->input('quantidade') ?: $request->input('quantidade[]') ?: [];
            $valoresUnit = $request->input('valor_unitario') ?: $request->input('valor_unitario[]') ?: [];
            $subtotais = $request->input('subtotal_item') ?: $request->input('subtotal_item[]') ?: [];

            if (!is_array($produtoIds) || count($produtoIds) === 0) {
                return response()->json(['message' => 'Itens da venda são obrigatórios'], 400);
            }

            $parseMoney = function ($value): float {
                if ($value === null) return 0.0;
                if (is_numeric($value)) return (float)$value;
                return (float)__convert_value_bd((string)$value);
            };

            $desconto = $parseMoney($request->desconto);
            $acrescimo = $parseMoney($request->acrescimo);

            $venda = DB::transaction(function () use (
                $request,
                $empresaId,
                $usuarioId,
                $produtoIds,
                $quantidades,
                $valoresUnit,
                $subtotais,
                $desconto,
                $acrescimo,
                $parseMoney
            ) {
                $total = 0.0;
                foreach ($produtoIds as $idx => $produtoId) {
                    $st = $subtotais[$idx] ?? null;
                    if ($st !== null) {
                        $total += $parseMoney($st);
                    } else {
                        $qtd = $parseMoney($quantidades[$idx] ?? 1);
                        $vu = $parseMoney($valoresUnit[$idx] ?? 0);
                        $total += ($qtd * $vu);
                    }
                }

                $venda = VendaCaixa::create([
                    'empresa_id' => $empresaId,
                    'usuario_id' => $usuarioId,
                    'cliente_id' => $request->cliente_id ?: null,
                    'valor_total' => ($total + $acrescimo) - $desconto,
                    'estado_emissao' => 'novo',
                    'tipo_pagamento' => '',
                    'dinheiro_recebido' => 0,
                    'troco' => 0,
                    'nome' => $request->cliente_nome ?? '',
                    'cpf' => $request->cliente_cpf_cnpj ?? '',
                    'observacao' => $request->observacao ?? '',
                    'desconto' => $desconto,
                    'acrescimo' => $acrescimo,
                    'rascunho' => 1,
                    'consignado' => 0,
                    'pdv_java' => 0,
                    'pedido_delivery_id' => 0,
                    'qr_code_base64' => 0,
                    'filial_id' => $request->filial_id != -1 ? $request->filial_id : null,
                ]);

                foreach ($produtoIds as $idx => $produtoId) {
                    $produto = Produto::where('empresa_id', $empresaId)->findOrFail((int)$produtoId);
                    $qtd = $parseMoney($quantidades[$idx] ?? 1);
                    $vu = $parseMoney($valoresUnit[$idx] ?? 0);
                    ItemVendaCaixa::create([
                        'venda_caixa_id' => $venda->id,
                        'produto_id' => (int)$produtoId,
                        'quantidade' => $qtd,
                        'valor' => $vu,
                        'valor_custo' => $produto->valor_compra ?? 0,
                        'cfop' => 0,
                        'observacao' => '',
                        'item_pedido_id' => null,
                    ]);
                }

                return $venda;
            });

            return response()->json(['id' => $venda->id], 200);
        } catch (\Exception $e) {
            __saveLogError($e, request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function vendasSuspensas(Request $request)
    {
        $empresaId = (int)($request->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return response()->json(['message' => 'empresa_id é obrigatório'], 400);
        }

        $vendas = VendaCaixa::with(['cliente', 'usuario'])
            ->where('empresa_id', $empresaId)
            ->where('rascunho', 1)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $html = view('frontBox.partials.vendas_suspensas_rows', compact('vendas'))->render();
        return response($html, 200);
    }

    public function deleteVendaSuspensa(Request $request, int $id)
    {
        try {
            $empresaId = (int)($request->empresa_id ?? 0);
            if ($empresaId <= 0) {
                return response()->json(['message' => 'empresa_id é obrigatório'], 400);
            }

            $venda = VendaCaixa::where('empresa_id', $empresaId)
                ->where('rascunho', 1)
                ->findOrFail($id);

            DB::transaction(function () use ($venda) {
                $venda->itens()->delete();
                $venda->fatura()->delete();
                $venda->delete();
            });

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            __saveLogError($e, request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
