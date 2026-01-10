<?php

namespace App\Http\Controllers\Petshop\Animais;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Pelagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnimalPelagemController extends Controller
{

  public function index(Request $request)
  {
    $empresaId = Auth::user()?->empresa?->empresa_id;

    $pesquisa = $request->input('pesquisa');

    $query = Pelagem::where('empresa_id', $empresaId)
      ->when($pesquisa, function ($q) use ($pesquisa) {
        $q->where('nome', 'LIKE', "%{$pesquisa}%");
      });

    $data = $query->paginate(env("PAGINACAO"))->appends($request->all());

    return view('petshop.animais.pelagens.index', compact('data'));
  }

  public function create()
  {
    return view('petshop.animais.pelagens.create');
  }

  public function store(Request $request)
  {
    $empresa_id = Auth::user()?->empresa?->empresa_id;

    $request->validate([
      'nome' => 'required',
    ]);

    $hasAnotherPelagem = Pelagem::where('nome', $request->nome)
      ->where('empresa_id', $request->empresa_id)
      ->first();

    if ($hasAnotherPelagem) {
      session()->flash("flash_error", "Já existe uma pelagem com este nome.");
      return redirect()->back()->withInput();
    }

    try {
      Pelagem::create([
        'nome' => $request->nome,
        'empresa_id' => $empresa_id,
      ]);

      session()->flash("flash_success", "Pelagem cadastrada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.pelagens.index');
  }

  public function edit($id)
  {
    $item = Pelagem::findOrFail($id);
    __validaObjetoEmpresa($item);

    return view('petshop.animais.pelagens.edit', compact('item'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'nome' => 'required',
    ]);


    try {
      $item = Pelagem::findOrFail($id);
      __validaObjetoEmpresa($item);

      $hasAnotherPelagem = Pelagem::where('nome', $request->nome)
        ->where('empresa_id', $request->empresa_id)
        ->first();

      if ($hasAnotherPelagem && $item->nome != $request->nome) {
        session()->flash("flash_error", "Já existe uma pelagem com este nome.");
        return redirect()->back()->withInput();
      }

      $item->update([
        'nome' => $request->nome,
      ]);

      session()->flash("flash_success", "Pelagem atualizada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.pelagens.index');
  }

  public function destroy($id)
  {
    try {
      $item = Pelagem::findOrFail($id);
      __validaObjetoEmpresa($item);

      $item->delete();

      session()->flash("flash_success", "Pelagem excluída com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.pelagens.index');
  }
}
