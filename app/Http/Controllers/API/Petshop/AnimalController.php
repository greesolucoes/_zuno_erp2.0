<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Pelagem;
use App\Models\Petshop\Raca;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnimalController extends Controller
{
    public function search (Request $request)
    {
        $data = Animal::where('empresa_id', $request->empresa_id)
            ->where(function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->pesquisa . '%')
                ->orWhereHas('cliente', function ($query) use ($request) {
                    $query->where('razao_social', 'like', '%' . $request->pesquisa . '%');
                });
            })
            ->when(!empty($request->cliente_id), function ($query) use ($request) {
                $query->where('cliente_id', $request->cliente_id);
            })
            ->with('cliente')
            ->orderBy('nome')
            ->get();

        if ($data->isEmpty()) {
            return response()->json(['success' => 'false', 'message' => 'Nenhum animal encontrado.'], 404);
        }

        return response()->json(['success' => 'true', 'data' => $data], 200);
    }

    public function searchEspecie(Request $request)
    {
        $data = Especie::where('empresa_id', $request->empresa_id)
            ->where('nome', 'like', '%' . $request->pesquisa . '%')
            ->orderBy('nome')
            ->get();

        if ($data->isEmpty()) {
            return response()->json(['success' => 'false', 'message' => 'Nenhuma espécie encontrada.'], 404);
        }

        return response()->json(['success' => 'true', 'data' => $data], 200);
    }

    public function storeEspecie(Request $request)
    {
        $request->validate(['nome' => ['required', Rule::unique('petshop_animais_especies')->where('empresa_id', $request->empresa_id),],], ['nome.unique' => 'Já existe uma espécie com este nome.',]);
        try {
            $item = Especie::create(['empresa_id' => $request->empresa_id, 'nome' => $request->nome]);

            return response()->json($item, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function searchPelagem(Request $request)
    {
        $data = Pelagem::where('empresa_id', $request->empresa_id)
            ->where('nome', 'like', '%' . $request->pesquisa . '%')
            ->orderBy('nome')
            ->get();

        if ($data->isEmpty()) {
            return response()->json(['success' => 'false', 'message' => 'Nenhuma pelagem encontrada.'], 404);
        }

        return response()->json(['success' => 'true', 'data' => $data], 200);
    }

    public function storePelagem(Request $request)
    {
        $request->validate(['nome' => ['required', Rule::unique('petshop_animais_pelagens')->where('empresa_id', $request->empresa_id),],], ['nome.unique' => 'Já existe uma pelagem com este nome.',]);
        try {
            $item = Pelagem::create(['empresa_id' => $request->empresa_id, 'nome' => $request->nome]);

            return response()->json($item, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function searchRaca(Request $request)
    {
        $data = Raca::where('empresa_id', $request->empresa_id)
            ->where('especie_id', $request->especie_id)
            ->where('nome', 'like', '%' . $request->pesquisa . '%')
            ->orderBy('nome')
        ->get();

        if ($data->isEmpty()) {
            return response()->json(['success' => 'false', 'message' => 'Nenhuma raça encontrada.'], 404);
        }

        return response()->json(['success' => 'true', 'data' => $data], 200);
    }

    public function storeRaca(Request $request)
    {
        $request->validate(['nome' => ['required', Rule::unique('petshop_animais_racas')->where('empresa_id', $request->empresa_id),],], ['nome.unique' => 'Já existe uma raça com este nome.',]);
        try {
            $item = Raca::create(['empresa_id' => $request->empresa_id, 'nome' => $request->nome, 'especie_id' => $request->especie_id ?? null]);

            return response()->json($item, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 200);
        }
    }
}
