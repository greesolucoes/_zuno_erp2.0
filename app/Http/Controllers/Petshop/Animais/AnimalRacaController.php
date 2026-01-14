<?php

namespace App\Http\Controllers\Petshop\Animais;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Raca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimalRacaController extends Controller
{

  public function index(Request $request)
  {
    $empresaId = request()->empresa_id;

    $pesquisa = $request->input('pesquisa');

    $query = Raca::where('empresa_id', $empresaId)

      ->when($pesquisa, function ($q) use ($pesquisa) {
        $q->where(function ($subQuery) use ($pesquisa) {
            
              $subQuery->where('nome', 'LIKE', "%{$pesquisa}%");
           
        });
      });

    $data = $query->paginate(env("PAGINACAO"))->appends($request->all());

    return view('petshop.animais.racas.index', compact('data'));
  }

  public function create()
  {
    $empresaId = request()->empresa_id;

    $especies = Especie::where('empresa_id', $empresaId)->get();

    return view('petshop.animais.racas.create', compact('especies'));
  }

  public function store(Request $request)
  {
    $empresaId = request()->empresa_id;
    $this->_validate($request);

    $hasAnotherRaca = Raca::where('nome', $request->nome)
      ->where('empresa_id', $empresaId)
      ->where('especie_id', $request->especie_id)
      ->first();

    if ($hasAnotherRaca) {
      session()->flash("flash_erro", "Já existe uma raça com este nome para esta espécie.");
      return redirect()->back()->withInput();
    }

    try {
      DB::transaction(function () use ($request, $empresaId) {
        Raca::create([
          'nome' => $request->nome,
          'especie_id' => $request->especie_id,
          'empresa_id' => $empresaId,
        ]);
      });

      session()->flash("flash_sucesso", "Raça cadastrada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
      __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('animais.racas.index');
  }

  public function edit(Request $request, $id)
  {
    $item = Raca::findOrFail($id);

    $especies = Especie::where('empresa_id', request()->empresa_id)->get();

    return view('petshop.animais.racas.edit', compact('item', 'especies'));
  }

  public function update(Request $request, $id)
  {
    $empresaId = request()->empresa_id;
    $this->_validate($request);


    try {
      $item = Raca::findOrFail($id);

      $hasAnotherRaca = Raca::where('nome', $request->nome)
        ->where('empresa_id', $empresaId)
        ->where('especie_id', $request->especie_id)
        ->first();

      if ($hasAnotherRaca && $item->nome != $request->nome) {
        session()->flash("flash_erro", "Já existe uma raça com este nome para esta espécie.");
        return redirect()->back()->withInput();
      }

      DB::transaction(function () use ($request, $item) {
        $item->update([
          'nome' => $request->nome,
          'especie_id' => $request->especie_id,
        ]);
      });

      session()->flash("flash_sucesso", "Raça atualizada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
      __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('animais.racas.index');
  }

  public function destroy($id)
  {
    try {
      $item = Raca::findOrFail($id);

      DB::transaction(function () use ($item) {
        $item->delete();
      });

      session()->flash("flash_sucesso", "Raça excluída com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
      __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('animais.racas.index');
  }

  private function _validate(Request $request)
  {
    $rules = [
      'nome' => 'required|max:255',
      'especie_id' => 'required',
    ];
    $messages = [
      'nome.required' => 'O nome é obrigatório.',
      'especie_id.required' => 'A espécie é obrigatória.',
    ];
    $this->validate($request, $rules, $messages);
  }
}
