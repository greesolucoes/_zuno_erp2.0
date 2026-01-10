<?php

namespace App\Http\Controllers\Petshop\Animais;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Raca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnimalRacaController extends Controller
{

  public function index(Request $request)
  {
    $empresa_id = Auth::user()?->empresa?->empresa_id;

    $pesquisa = $request->input('pesquisa');

    $query = Raca::where('empresa_id', $empresa_id)

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
    $empresa_id = Auth::user()?->empresa?->empresa_id;

    $especies = Especie::where('empresa_id', $empresa_id)->get();

    return view('petshop.animais.racas.create', compact('especies'));
  }

  public function store(Request $request)
  {
    $empresa_id = Auth::user()?->empresa?->empresa_id;

    $request->validate(
      [
        'nome' => 'required',
        'especie_id' => 'required',
      ],
    );

    $hasAnotherRaca = Raca::where('nome', $request->nome)
      ->where('empresa_id', $empresa_id)
      ->where('especie_id', $request->especie_id)
      ->first();

    if ($hasAnotherRaca) {
      session()->flash("flash_error", "Já existe uma raça com este nome para esta espécie.");
      return redirect()->back()->withInput();
    }

    try {
      Raca::create([
        'nome' => $request->nome,
        'especie_id' => $request->especie_id,
        'empresa_id' => $empresa_id,
      ]);

      session()->flash("flash_success", "Raça cadastrada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.racas.index');
  }

  public function edit(Request $request, $id)
  {
    $item = Raca::findOrFail($id);
    __validaObjetoEmpresa($item);

    $especies = Especie::where('empresa_id', $request->empresa_id)->get();

    return view('petshop.animais.racas.edit', compact('item', 'especies'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'nome' => 'required',
    ]);


    try {
      $item = Raca::findOrFail($id);
      __validaObjetoEmpresa($item);

      $hasAnotherRaca = Raca::where('nome', $request->nome)
        ->where('empresa_id', $request->empresa_id)
        ->where('especie_id', $request->especie_id)
        ->first();

      if ($hasAnotherRaca && $item->nome != $request->nome) {
        session()->flash("flash_error", "Já existe uma raça com este nome para esta espécie.");
        return redirect()->back()->withInput();
      }

      $item->update([
        'nome' => $request->nome,
        'especie_id' => $request->especie_id,
      ]);

      session()->flash("flash_success", "Raça atualizada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.racas.index');
  }

  public function destroy($id)
  {
    try {
      $item = Raca::findOrFail($id);
      __validaObjetoEmpresa($item);

      $item->delete();

      session()->flash("flash_success", "Raça excluída com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.racas.index');
  }
}
