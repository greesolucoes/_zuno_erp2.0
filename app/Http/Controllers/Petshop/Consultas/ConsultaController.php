<?php

namespace App\Http\Controllers\Petshop\Consultas;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Consulta;
use App\Models\Petshop\Diagnostico;
use App\Models\Petshop\Exame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultaController extends Controller
{
    public function index(Request $request)
    {
      $empresaId = request()->empresa_id;

      $data = Consulta::where('empresa_id', $empresaId)
      ->with('animal')
      ->paginate(env("PAGINACAO"));

      return view('petshop.animais.consultas.index', compact('data'));
    }

    public function create()
    {
      $empresaId = request()->empresa_id;

      $animais = Animal::where('empresa_id', $empresaId)->get();
      $diagnosticos = Diagnostico::where('empresa_id', $empresaId)->get();
      $exames = Exame::where('empresa_id', $empresaId)->get();

      return view('petshop.animais.consultas.create', compact( 'animais', "diagnosticos", "exames"));
    }

    public function store(Request $request)
    {
      $empresaId = request()->empresa_id;
      $this->_validate($request);

      try {
        DB::transaction(function () use ($request, $empresaId) {
          Consulta::create([
            'animal_id' => $request->animal_id,
            'diagnostico_id' => $request->diagnostico_id,
            'exame_id' => $request->exame_id,
            'datahora_consulta' => $request->datahora_consulta,
            'status' => $request->status,
            'observacao' => $request->observacao,
            'empresa_id' => $empresaId,
          ]);
        });

        session()->flash("flash_sucesso", "Consulta cadastrada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
      }

      return redirect()->route('animais.consultas.index');
    }

    public function edit($id)
    {
      $empresaId = request()->empresa_id;

      $item = Consulta::findOrFail($id);
      __validaObjetoEmpresa($item);

      $animais = Animal::where('empresa_id', $empresaId)->get();
      $diagnosticos = Diagnostico::where('empresa_id', $empresaId)->get();
      $exames = Exame::where('empresa_id', $empresaId)->get();

      $item->datahora_consulta = date('Y-m-d h:i', strtotime($item->datahora_consulta));

      return view('petshop.animais.consultas.edit', compact('item', 'animais', 'diagnosticos', "exames"));
    }

    public function update(Request $request, $id)
    {
      $this->_validate($request);

      try {
        $item = Consulta::findOrFail($id);
        __validaObjetoEmpresa($item);

        DB::transaction(function () use ($request, $item) {
          $item->update([
              'animal_id' => $request->animal_id,
              'diagnostico_id' => $request->diagnostico_id,
              'exame_id' => $request->exame_id,
              'datahora_consulta' => $request->datahora_consulta,
              'status' => $request->status,
              'observacao' => $request->observacao,
          ]);
        });

        session()->flash("flash_sucesso", "Consulta atualizada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
      }

      return redirect()->route('animais.consultas.index');
    }

    public function destroy($id)
    {
      try {
        $item = Consulta::findOrFail($id);
        __validaObjetoEmpresa($item);

        DB::transaction(function () use ($item) {
          $item->delete();
        });

        session()->flash("flash_sucesso", "Consulta excluída com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
      }

      return redirect()->route('animais.consultas.index');
    }

    private function _validate(Request $request)
    {
      $rules = [
        'animal_id' => 'required',
        'diagnostico_id' => 'nullable',
        'exame_id' => 'required',
        'datahora_consulta' => 'required',
        'status' => 'required',
      ];
      $messages = [
        'animal_id.required' => 'O animal é obrigatório.',
        'exame_id.required' => 'O exame é obrigatório.',
        'datahora_consulta.required' => 'A data/hora é obrigatória.',
        'status.required' => 'O status é obrigatório.',
      ];
      $this->validate($request, $rules, $messages);
    }

}
