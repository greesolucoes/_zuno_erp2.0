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

        if (!$localId) {
            return response()->json(['usar_agendamento_alternativo' => false]);
        }

        $config = Configuracao::where('localizacao_id', $localId)->first();

        return response()->json([
            'usar_agendamento_alternativo' => (bool) optional($config)->usar_agendamento_alternativo,
        ]);
    }
}