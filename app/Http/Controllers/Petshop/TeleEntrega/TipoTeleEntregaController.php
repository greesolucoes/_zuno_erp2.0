<?php

namespace App\Http\Controllers\Petshop\TeleEntrega;

use App\Http\Controllers\Controller;
use App\Models\Petshop\TipoTeleEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoTeleEntregaController extends Controller
{

    public function index(Request $request)
    {
      $empresaId = request()->empresa_id;

      $data = TipoTeleEntrega::where('empresa_id', $empresaId)
      ->paginate(env("PAGINACAO"));

      return view('tele_entregas.tipos.index', compact('data'));
    }

    public function create()
    {
      return view('tele_entregas.tipos.create',);
    }

    public function store(Request $request)
    {
      $empresaId = request()->empresa_id;
      $this->_validate($request);

      $hasAnotherTipeTeleEntrega = TipoTeleEntrega::where('empresa_id', $empresaId)
        ->where('nome', $request->nome)
        ->first();

      if ($hasAnotherTipeTeleEntrega) {
        \session()->flash("flash_warning", "Já existe um tipo de tele-entrega com este nome!");
        return redirect()->route('tipos_tele_entregas.create')->withInput();
      }

      try {
        DB::transaction(function () use ($request, $empresaId) {
          TipoTeleEntrega::create([
            'nome' => $request->nome,
            'empresa_id' => $empresaId,
          ]);
        });

        session()->flash("flash_sucesso", "Tipo de tele-entrega cadastrada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
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
      $empresaId = request()->empresa_id;
      $this->_validate($request);

      try {
        $item = TipoTeleEntrega::findOrFail($id);
        __validaObjetoEmpresa($item);

        if ($item->nome == $request->nome) {
          \session()->flash("flash_warning", "Nenhuma alteração foi feita");
          return redirect()->route('tipos_tele_entregas.index');
        }

        $hasAnotherTipeTeleEntrega = TipoTeleEntrega::where('empresa_id', $empresaId)
          ->where('nome', $request->nome)
          ->first();

        if ($hasAnotherTipeTeleEntrega) {
          \session()->flash("flash_warning", "Já existe um tipo de tele-entrega com este nome!");
          return redirect()->route('tipos_tele_entregas.edit', [$id])->withInput();
        }

        DB::transaction(function () use ($request, $item) {
          $item->update([
            'nome' => $request->nome,
          ]);
        });

        session()->flash("flash_sucesso", "Tipo de tele-entrega atualizada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
      }

      return redirect()->route('tipos_tele_entregas.index');
    }

    public function destroy($id)
    {
      try {
        $item = TipoTeleEntrega::findOrFail($id);
        __validaObjetoEmpresa($item);

        DB::transaction(function () use ($item) {
          $item->delete();
        });

        session()->flash("flash_sucesso", "Tipo de tele-entrega excluída com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
      }

      return redirect()->route('tipos_tele_entregas.index');
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
