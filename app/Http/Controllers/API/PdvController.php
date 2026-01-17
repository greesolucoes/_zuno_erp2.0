<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AberturaCaixa;
use App\Models\ConfigCaixa;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;

class PdvController extends Controller
{
    public function tiposPagamento(Request $request)
    {
        try {
            $usuarioId = (int)($request->usuario_id ?? 0);
            if ($usuarioId <= 0) {
                return response()->json(['message' => 'usuario_id é obrigatório'], 400);
            }

            $todos = VendaCaixa::tiposPagamento();

            $config = ConfigCaixa::where('usuario_id', $usuarioId)->first();
            $habilitados = [];
            if ($config && $config->tipos_pagamento) {
                $habilitados = json_decode($config->tipos_pagamento, true) ?: [];
            }

            $lista = [];
            foreach ($todos as $codigo => $nome) {
                if (
                    $habilitados &&
                    !in_array((string)$codigo, $habilitados, true) &&
                    !in_array((int)$codigo, $habilitados, true)
                ) {
                    continue;
                }
                $lista[] = [
                    'id' => (string)$codigo,
                    'nome' => $nome,
                ];
            }

            return response()->json([
                'lista' => $lista,
                'detalhes' => [],
            ], 200);
        } catch (\Exception $e) {
            __saveLogError($e, request()->empresa_id);
            return response()->json(['message' => 'Algo deu errado: ' . $e->getMessage()], 500);
        }
    }

    public function suprimento(Request $request)
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

            $abertura = AberturaCaixa::where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('status', 0)
                ->orderBy('id', 'desc')
                ->first();
            if (!$abertura) {
                return response()->json(['message' => 'Caixa não está aberto'], 400);
            }

            $valor = __convert_value_bd((string)($request->valor ?? '0'));
            if ($valor <= 0) {
                return response()->json(['message' => 'Valor inválido'], 400);
            }

            $item = SuprimentoCaixa::create([
                'usuario_id' => $usuarioId,
                'valor' => $valor,
                'observacao' => $request->observacao ?? '',
                'empresa_id' => $empresaId,
            ]);

            return response()->json(['suprimento_id' => $item->id], 200);
        } catch (\Exception $e) {
            __saveLogError($e, request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function sangria(Request $request)
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

            $abertura = AberturaCaixa::where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('status', 0)
                ->orderBy('id', 'desc')
                ->first();
            if (!$abertura) {
                return response()->json(['message' => 'Caixa não está aberto'], 400);
            }

            $valor = __convert_value_bd((string)($request->valor ?? '0'));
            if ($valor <= 0) {
                return response()->json(['message' => 'Valor inválido'], 400);
            }

            if ($valor > $this->somaTotalEmCaixa($empresaId, $abertura)) {
                return response()->json(['message' => 'Valor de sangria ultrapassa valor em caixa!'], 400);
            }

            $item = SangriaCaixa::create([
                'usuario_id' => $usuarioId,
                'valor' => $valor,
                'observacao' => $request->observacao ?? '',
                'empresa_id' => $empresaId,
            ]);

            return response()->json(['sangria_id' => $item->id], 200);
        } catch (\Exception $e) {
            __saveLogError($e, request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function printSangria(Request $request, int $id)
    {
        $empresaId = (int)($request->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return response('empresa_id é obrigatório', 400);
        }

        $item = SangriaCaixa::where('empresa_id', $empresaId)->findOrFail($id);
        $html = '<html><head><meta charset="utf-8"><title>Sangria</title></head><body>';
        $html .= '<h2>Sangria #' . $item->id . '</h2>';
        $html .= '<p><b>Data:</b> ' . __data_pt($item->created_at, 1) . '</p>';
        $html .= '<p><b>Valor:</b> ' . __moeda($item->valor) . '</p>';
        $html .= '<p><b>Observação:</b> ' . e($item->observacao) . '</p>';
        $html .= '</body></html>';
        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function printSuprimento(Request $request, int $id)
    {
        $empresaId = (int)($request->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return response('empresa_id é obrigatório', 400);
        }

        $item = SuprimentoCaixa::where('empresa_id', $empresaId)->findOrFail($id);
        $html = '<html><head><meta charset="utf-8"><title>Suprimento</title></head><body>';
        $html .= '<h2>Suprimento #' . $item->id . '</h2>';
        $html .= '<p><b>Data:</b> ' . __data_pt($item->created_at, 1) . '</p>';
        $html .= '<p><b>Valor:</b> ' . __moeda($item->valor) . '</p>';
        $html .= '<p><b>Observação:</b> ' . e($item->observacao) . '</p>';
        $html .= '</body></html>';
        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }

    private function somaTotalEmCaixa(int $empresaId, AberturaCaixa $abertura): float
    {
        $soma = 0;
        $soma += (float)($abertura->valor ?? 0);

        $vendasPdv = VendaCaixa::whereBetween('id', [
            $abertura->primeira_venda_nfce,
            ($abertura->primeira_venda_nfce > 0 ? $abertura->primeira_venda_nfce : 1) * 10000,
        ])
            ->selectRaw('sum(valor_total) as valor')
            ->where('empresa_id', $empresaId)
            ->first();
        if ($vendasPdv != null) {
            $soma += (float)($vendasPdv->valor ?? 0);
        }

        $vendas = Venda::whereBetween('id', [
            $abertura->primeira_venda_nfe,
            ($abertura->primeira_venda_nfe > 0 ? $abertura->primeira_venda_nfce : 1) * 10000,
        ])
            ->selectRaw('sum(valor_total) as valor')
            ->where('empresa_id', $empresaId)
            ->first();
        if ($vendas != null) {
            $soma += (float)($vendas->valor ?? 0);
        }

        $amanha = date('Y-m-d', strtotime('+1 days')) . " 00:00:00";
        $suprimentosSoma = SuprimentoCaixa::selectRaw('sum(valor) as valor')
            ->whereBetween('created_at', [$abertura->created_at, $amanha])
            ->where('empresa_id', $empresaId)
            ->first();
        if ($suprimentosSoma != null) {
            $soma += (float)($suprimentosSoma->valor ?? 0);
        }

        $sangriasSoma = SangriaCaixa::selectRaw('sum(valor) as valor')
            ->whereBetween('created_at', [$abertura->created_at, $amanha])
            ->where('empresa_id', $empresaId)
            ->first();
        if ($sangriasSoma != null) {
            $soma -= (float)($sangriasSoma->valor ?? 0);
        }

        return (float)$soma;
    }
}
