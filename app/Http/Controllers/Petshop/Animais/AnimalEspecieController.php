<?php

namespace App\Http\Controllers\Petshop\Animais;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Especie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AnimalEspecieController extends Controller
{

  public function index(Request $request)
  {
    $empresaId = Auth::user()?->empresa?->empresa_id;

    $pesquisa = $request->input('pesquisa');

    $query = Especie::where('empresa_id', $empresaId)
      ->when($pesquisa, function ($q) use ($pesquisa) {
        $q->where('nome', 'LIKE', "%{$pesquisa}%");
      });

    $data = $query->paginate(env("PAGINACAO"))->appends($request->all());

    return view('petshop.animais.especies.index', compact('data'));
  }

  public function create()
  {
    return view('petshop.animais.especies.create');
  }

  public function store(Request $request)
  {
    $empresa_id = Auth::user()?->empresa?->empresa_id;

    $request->validate([
      'nome' => [
        'required',
        Rule::unique('animais_especies')
          ->where(function ($query) use ($request) {
            return $query->where('empresa_id', Auth::user()?->empresa?->empresa_id)
              ->whereRaw('LOWER(nome) = ?', [strtolower($request->nome)]);
          }),
      ],
    ], [
      'nome.unique' => 'Já existe uma espécie com esse nome.',
      'nome.required' => 'O nome da espécie é obrigatório.',
    ]);

    try {
      Especie::create([
        'nome' => $request->nome,
        'empresa_id' => $empresa_id,
      ]);

      session()->flash("flash_success", "Espécie cadastrada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.especies.index');
  }

  public function edit($id)
  {
    $item = Especie::findOrFail($id);
    __validaObjetoEmpresa($item);

    return view('petshop.animais.especies.edit', compact('item'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'nome' => [
        'required',
        Rule::unique('animais_especies')
          ->where(function ($query) use ($request) {
            return $query->where('empresa_id', Auth::user()?->empresa?->empresa_id)
              ->whereRaw('LOWER(nome) = ?', [strtolower($request->nome)]);
          }),
      ],
    ], [
      'nome.unique' => 'Já existe uma espécie com esse nome.',
      'nome.required' => 'O nome da espécie é obrigatório.',
    ]);

    try {
      $item = Especie::findOrFail($id);
      __validaObjetoEmpresa($item);

      $item->update([
        'nome' => $request->nome,
      ]);

      session()->flash("flash_success", "Espécie atualizada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.especies.index');
  }

  public function destroy($id)
  {
    try {
      $item = Especie::findOrFail($id);
      __validaObjetoEmpresa($item);

      $item->delete();

      session()->flash("flash_success", "Espécie excluída com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.especies.index');
  }
}
