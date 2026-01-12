<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Animal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    public function pesquisa(Request $request)
    {
        $data = Animal::orderBy('nome', 'desc')
            ->where('empresa_id', $request->empresa_id)
            ->where('nome', 'like', "%$request->pesquisa%")
            ->when(!empty($request->cliente_id), function ($q) use ($request) {
                $q->where('cliente_id', $request->cliente_id);
            })
            ->with('cliente')
            ->get();
        // filtrar dado cliente

        return response()->json($data, 200);
    }

    public function store1(Request $request)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;


        $request->validate([
            'cliente_id'      => 'required',
            'especie_id'      => 'required',
            'raca_id'         => 'required',
            'nome'            => 'required',
            'sexo'            => 'required',
            'tem_pedigree'    => 'required',
            'porte'           => 'required',
        ]);

        try {
            Animal::create([
                'cliente_id'      => $request->cliente_id,
                'especie_id'      => $request->especie_id,
                'raca_id'         => $request->raca_id,
                'pelagem_id'      => $request->pelagem_id,
                'nome'            => $request->nome,
                'data_nascimento' => $request->data_nascimento,
                'peso'            => $request->peso,
                'sexo'            => $request->sexo,
                'idade'           => Carbon::parse($request->data_nascimento)->age,
                'tem_pedigree'    => $request->tem_pedigree === 'S' ? true : false,
                'porte'           => $request->porte,
                'chip'            => $request->chip,
                'pedigree'        => $request->pedigree,
                'origem'          => $request->origem,
                'observacao'      => $request->observacao,
                'empresa_id'      => $empresa_id,
            ]);

            session()->flash("flash_success", "Paciente cadastrado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('animais.pacientes.index');
    }

    public function store(Request $request)
    {
        // Verificar se já existe um animal com esse nome e cliente (opcional)
        $animalExistente = Animal::where('empresa_id', $request->empresa_id)
            ->where('nome', $request->nome)
            ->where('cliente_id', $request->cliente_id)
            ->first();

        if ($animalExistente) {
            return response()->json("Animal já cadastrado para este cliente", 401);
        }

        if ($request->filled('data_nascimento')) {
            $parts = explode('/', $request->data_nascimento);

            if (count($parts) === 3) {
                $request->merge([
                    'data_nascimento' => $parts[2] . '-' . $parts[1] . '-' . $parts[0]
                ]);
            }
        }

        // Criação do animal
        $animal = Animal::create($request->all());

        return response()->json($animal, 200);
    }
    public function update(Request $request)
    {
        $animal = Animal::findOrFail($request->id);

        if ($request->filled('data_nascimento')) {
            $parts = explode('/', $request->data_nascimento);
            if (count($parts) === 3) {
                $request->merge([
                    'data_nascimento' => $parts[2] . '-' . $parts[1] . '-' . $parts[0]
                ]);
            }
        }

        $animal->update($request->all());

        $animal->load('especie', 'raca', 'pelagem');

        return response()->json($animal, 200);
    }
}
