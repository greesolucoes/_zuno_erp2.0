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
            return response()->json(['success' => 'true', 'data' => []], 200);
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
            return response()->json(['success' => 'true', 'data' => []], 200);
        }

        return response()->json(['success' => 'true', 'data' => $data], 200);
    }

    public function storeEspecie(Request $request)
    {
        try {
            $this->_validateEspecie($request);

            $item = Especie::create(['empresa_id' => $request->empresa_id, 'nome' => $request->nome]);

            return response()->json($item, 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function searchPelagem(Request $request)
    {
        $data = Pelagem::where('empresa_id', $request->empresa_id)
            ->where('nome', 'like', '%' . $request->pesquisa . '%')
            ->orderBy('nome')
            ->get();

        if ($data->isEmpty()) {
            return response()->json(['success' => 'true', 'data' => []], 200);
        }

        return response()->json(['success' => 'true', 'data' => $data], 200);
    }

    public function storePelagem(Request $request)
    {
        try {
            $this->_validatePelagem($request);

            $item = Pelagem::create(['empresa_id' => $request->empresa_id, 'nome' => $request->nome]);

            return response()->json($item, 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
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
            return response()->json(['success' => 'true', 'data' => []], 200);
        }

        return response()->json(['success' => 'true', 'data' => $data], 200);
    }

    public function storeRaca(Request $request)
    {
        try {
            $this->_validateRaca($request);

            $item = Raca::create(['empresa_id' => $request->empresa_id, 'nome' => $request->nome, 'especie_id' => $request->especie_id ?? null]);

            return response()->json($item, 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    private function _validateEspecie(Request $request)
    {
        $rules = [
            'empresa_id' => 'required',
            'nome' => [
                'required',
                Rule::unique('petshop_animais_especies')->where('empresa_id', $request->empresa_id),
            ],
        ];
        $messages = [
            'nome.unique' => 'Já existe uma espécie com este nome.',
        ];
        $this->validate($request, $rules, $messages);
    }

    private function _validatePelagem(Request $request)
    {
        $rules = [
            'empresa_id' => 'required',
            'nome' => [
                'required',
                Rule::unique('petshop_animais_pelagens')->where('empresa_id', $request->empresa_id),
            ],
        ];
        $messages = [
            'nome.unique' => 'Já existe uma pelagem com este nome.',
        ];
        $this->validate($request, $rules, $messages);
    }

    private function _validateRaca(Request $request)
    {
        $rules = [
            'empresa_id' => 'required',
            'especie_id' => 'required',
            'nome' => [
                'required',
                Rule::unique('petshop_animais_racas')->where('empresa_id', $request->empresa_id),
            ],
        ];
        $messages = [
            'nome.unique' => 'Já existe uma raça com este nome.',
        ];
        $this->validate($request, $rules, $messages);
    }
}
