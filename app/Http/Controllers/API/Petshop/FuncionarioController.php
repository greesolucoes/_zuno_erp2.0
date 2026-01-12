<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\Filial;
use Illuminate\Http\Request;

class FuncionarioController extends Controller
{
    public function pesquisa(Request $request)
    {
        $data = Funcionario::where('empresa_id', $request->empresa_id)
            ->when($request->filled('pesquisa'), function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->pesquisa . '%');
            })
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(function ($item) {
                $item->cargo = $item->cargo ?? '';
                return $item;
            })
            ->values();

        return response()->json($data, 200);
    }

    public function index(Request $request)
    {
        $this->_validate($request);

        $localId = $request->query('local_id');

        if (!$localId) {
            return response()->json([]);
        }

        try {
            $local = Filial::select('empresa_id')->find($localId);
            if (!$local) {
                return response()->json([]);
            }

            $funcionarios = Funcionario::where('empresa_id', $local->empresa_id)
                ->orderBy('nome')
                ->get(['id', 'nome']);

            return response()->json($funcionarios, 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    private function _validate(Request $request)
    {
        $rules = [
            'local_id' => 'required',
        ];
        $messages = [];
        $this->validate($request, $rules, $messages);
    }
}
