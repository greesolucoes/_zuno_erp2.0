<?php

namespace App\Http\Controllers\Petshop\Animais;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Especie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AnimalEspecieController extends Controller
{

  public function index(Request $request)
  {
    $empresaId = request()->empresa_id;

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
    $empresaId = request()->empresa_id;
    $this->_validate($request);

    try {
      DB::transaction(function () use ($request, $empresaId) {
        Especie::create([
          'nome' => $request->nome,
          'empresa_id' => $empresaId,
        ]);
      });

      session()->flash("flash_sucesso", "Espécie cadastrada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
      __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('animais.especies.index');
  }

  public function edit($id)
  {
    $item = Especie::findOrFail($id);

    return view('petshop.animais.especies.edit', compact('item'));
  }

  public function update(Request $request, $id)
  {
    $this->_validate($request);

    try {
      $item = Especie::findOrFail($id);

      DB::transaction(function () use ($request, $item) {
        $item->update([
          'nome' => $request->nome,
        ]);
      });

      session()->flash("flash_sucesso", "Espécie atualizada com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
      __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('animais.especies.index');
  }

  public function destroy($id)
  {
    try {
      $item = Especie::findOrFail($id);

      DB::transaction(function () use ($item) {
        $item->delete();
      });

      session()->flash("flash_sucesso", "Espécie excluída com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
      __saveLogError($e, request()->empresa_id);
    }

    return redirect()->route('animais.especies.index');
  }

  private function _validate(Request $request)
  {
    $rules = [
      'nome' => [
        'required',
        Rule::unique('petshop_animais_especies')
          ->where(function ($query) use ($request) {
            return $query->where('empresa_id', request()->empresa_id)
              ->whereRaw('LOWER(nome) = ?', [strtolower($request->nome)]);
          }),
      ],
    ];
    $messages = [
      'nome.unique' => 'Já existe uma espécie com esse nome.',
      'nome.required' => 'O nome da espécie é obrigatório.',
    ];
    $this->validate($request, $rules, $messages);
  }
}
