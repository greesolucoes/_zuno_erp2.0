<?php

namespace App\Http\Controllers\Petshop\Animais;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Pelagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimalPelagemController extends Controller
{

  public function index(Request $request)
  {
    $empresaId = request()->empresa_id;

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
    $empresaId = request()->empresa_id;
    $this->_validate($request);

    $hasAnotherPelagem = Pelagem::where('nome', $request->nome)
      ->where('empresa_id', $empresaId)
      ->first();

    if ($hasAnotherPelagem) {
      session()->flash("flash_erro", "Já existe uma pelagem com este nome.");
      return redirect()->back()->withInput();
    }

    try {
      DB::transaction(function () use ($request, $empresaId) {
        Pelagem::create([
          'nome' => $request->nome,
          'empresa_id' => $empresaId,
        ]);
      });

      session()->flash("flash_sucesso", "Pelagem cadastrada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
      __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('animais.pelagens.index');
  }

  public function edit($id)
  {
    $item = Pelagem::findOrFail($id);

    return view('petshop.animais.pelagens.edit', compact('item'));
  }

  public function update(Request $request, $id)
  {
    $empresaId = request()->empresa_id;
    $this->_validate($request);


    try {
      $item = Pelagem::findOrFail($id);

      $hasAnotherPelagem = Pelagem::where('nome', $request->nome)
        ->where('empresa_id', $empresaId)
        ->first();

      if ($hasAnotherPelagem && $item->nome != $request->nome) {
        session()->flash("flash_erro", "Já existe uma pelagem com este nome.");
        return redirect()->back()->withInput();
      }

      DB::transaction(function () use ($request, $item) {
        $item->update([
          'nome' => $request->nome,
        ]);
      });

      session()->flash("flash_sucesso", "Pelagem atualizada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
      __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('animais.pelagens.index');
  }

  public function destroy($id)
  {
    try {
      $item = Pelagem::findOrFail($id);

      DB::transaction(function () use ($item) {
        $item->delete();
      });

      session()->flash("flash_sucesso", "Pelagem excluída com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
      __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('animais.pelagens.index');
  }

  private function _validate(Request $request)
  {
    $rules = [
      'nome' => 'required|max:255',
    ];
    $messages = [
      'nome.required' => 'O nome é obrigatório.',
    ];
    $this->validate($request, $rules, $messages);
  }
}
