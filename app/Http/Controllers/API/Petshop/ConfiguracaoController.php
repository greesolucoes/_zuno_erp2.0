<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Configuracao;
use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
    public function show(Request $request)
    {
        $localId = $request->query('local_id');
        $empresaId = $request->query('empresa_id');

        if (!$localId) {
            return response()->json(['usar_agendamento_alternativo' => false]);
        }

        try {
            $config = Configuracao::where('filial_id', $localId)
                ->when($empresaId, function ($query) use ($empresaId) {
                    $query->where('empresa_id', $empresaId);
                })
                ->first();

            return response()->json([
                'usar_agendamento_alternativo' => (bool) optional($config)->usar_agendamento_alternativo,
            ]);
        } catch (\Exception $e) {
            __saveLogError($e, $empresaId ?? request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
