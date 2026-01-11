<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class ServicoController extends Controller
{
    public function pesquisa(Request $request)
    {
        $categoria = $request->input('categoria');
        $categoriaNormalizada = $categoria ? Str::upper($categoria) : null;

        try {
            $data = Servico::where('empresa_id', $request->empresa_id)
                ->with('categoria')
                ->when($categoriaNormalizada, function ($query) use ($categoriaNormalizada) {
                    $query->whereHas('categoria', function ($sub) use ($categoriaNormalizada) {
                        $sub->whereRaw('UPPER(nome) = ?', [$categoriaNormalizada]);
                    });
                })
                ->when($request->only_petshop, function ($query) {
                    $query->whereHas('categoria', function ($sub) {
                        $sub->where('nome', 'CRECHE')
                        ->orWhere('nome', 'HOTEL')
                        ->orWhere('nome', 'ESTETICA');
                    });
                })
                ->when($request->without_petshop, function ($query) {
                    $query->whereHas('categoria', function ($sub) {
                        $sub->whereNot('nome', 'CRECHE')
                        ->orWhereNot('nome', 'HOTEL')
                        ->orWhereNot('nome', 'ESTETICA');
                    });
                })
                ->when(isset($request->is_frete), function ($query) {
                    $query->whereHas('categoria', function ($sub) {
                        $sub->where('nome', 'FRETE');
                    });
                })
                ->where('nome', 'LIKE', "%{$request->pesquisa}%")
                ->get();

            return response()->json($data, 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
