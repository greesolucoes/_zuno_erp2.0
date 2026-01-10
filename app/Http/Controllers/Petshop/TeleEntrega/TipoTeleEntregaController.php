<?php

namespace App\Http\Controllers\Petshop\TeleEntrega;

use App\Http\Controllers\Controller;
use App\Models\Petshop\TipoTeleEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TipoTeleEntregaController extends Controller
{

    public function index(Request $request)
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $data = TipoTeleEntrega::where('empresa_id', $empresa_id)
      ->paginate(env("PAGINACAO"));

      return view('tele_entregas.tipos.index', compact('data'));
    }

    public function create()
    {
      return view('tele_entregas.tipos.create',);
    }

    public function store(Request $request)
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $request->validate([
        'nome' => 'required',
      ]);

      $hasAnotherTipeTeleEntrega = TipoTeleEntrega::where('empresa_id', $empresa_id)->where('nome', $request->nome)->first();

      if ($hasAnotherTipeTeleEntrega) {
        \session()->flash("flash_warning", "Já existe um tipo de tele-entrega com este nome!");
        return redirect()->route('tipos_tele_entregas.create')->withInput();
      }

      try {
        TipoTeleEntrega::create([
          'nome' => $request->nome,
          'empresa_id' => $empresa_id,
        ]);

        session()->flash("flash_success", "Tipo de tele-entrega cadastrada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
      }

      return redirect()->route('tipos_tele_entregas.index');
    }

    public function edit($id)
    {
      $item = TipoTeleEntrega::findOrFail($id);
      __validaObjetoEmpresa($item);

      return view('tele_entregas.tipos.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
      $request->validate([
        'nome' => 'required',
      ]);

      try {
        $item = TipoTeleEntrega::findOrFail($id);
        __validaObjetoEmpresa($item);

        if ($item->nome == $request->nome) {
          \session()->flash("flash_warning", "Nenhuma alteração foi feita");
          return redirect()->route('tipos_tele_entregas.index');
        }

        $hasAnotherTipeTeleEntrega = TipoTeleEntrega::where('empresa_id', $request->empresa_id)->where('nome', $request->nome)->first();

        if ($hasAnotherTipeTeleEntrega) {
          \session()->flash("flash_warning", "Já existe um tipo de tele-entrega com este nome!");
          return redirect()->route('tipos_tele_entregas.edit', [$id])->withInput();
        }

        $item->update([
          'nome' => $request->nome,
        ]);

        session()->flash("flash_success", "Tipo de tele-entrega atualizada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
      }

      return redirect()->route('tipos_tele_entregas.index');
    }

    public function destroy($id)
    {
      try {
        $item = TipoTeleEntrega::findOrFail($id);
        __validaObjetoEmpresa($item);

        $item->delete();

        session()->flash("flash_success", "Tipo de tele-entrega excluída com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
      }

      return redirect()->route('tipos_tele_entregas.index');
    }

}
