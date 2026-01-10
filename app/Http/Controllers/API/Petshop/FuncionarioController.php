<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\Filial;
use Illuminate\Http\Request;

class FuncionarioController extends Controller
{
    public function index(Request $request)
    {
        $localId = $request->query('local_id');

        if (!$localId) {
            return response()->json([]);
        }

        $local = Filial::select('empresa_id')->find($localId);
        if (!$local) {
            return response()->json([]);
        }

        $funcionarios = Funcionario::where('empresa_id', $local->empresa_id)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json($funcionarios);
    }
}
