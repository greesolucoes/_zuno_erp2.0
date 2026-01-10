<?php

namespace App\Http\Controllers\Petshop\Consultas;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Consulta;
use App\Models\Petshop\Diagnostico;
use App\Models\Petshop\Exame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultaController extends Controller
{
    public function index(Request $request)
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $data = Consulta::where('empresa_id', $empresa_id)
      ->with('animal')
      ->paginate(env("PAGINACAO"));

      return view('petshop.animais.consultas.index', compact('data'));
    }

    public function create()
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $animais = Animal::where('empresa_id', $empresa_id)->get();
      $diagnosticos = Diagnostico::where('empresa_id', $empresa_id)->get();
      $exames = Exame::where('empresa_id', $empresa_id)->get();

      return view('petshop.animais.consultas.create', compact( 'animais', "diagnosticos", "exames"));
    }

    public function store(Request $request)
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $request->validate([
        'animal_id' => 'required',
        'diagnostico_id' => 'nullable',
        'exame_id' => 'required',
        'datahora_consulta' => 'required',
        'status' => 'required',
      ]);

      try {
        Consulta::create([
          'animal_id' => $request->animal_id,
          'diagnostico_id' => $request->diagnostico_id,
          'exame_id' => $request->exame_id,
          'datahora_consulta' => $request->datahora_consulta,
          'status' => $request->status,
          'observacao' => $request->observacao,
          'empresa_id' => $empresa_id,
        ]);

        session()->flash("flash_success", "Consulta cadastrada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
      }

      return redirect()->route('animais.consultas.index');
    }

    public function edit($id)
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $item = Consulta::findOrFail($id);
      __validaObjetoEmpresa($item);

      $animais = Animal::where('empresa_id', $empresa_id)->get();
      $diagnosticos = Diagnostico::where('empresa_id', $empresa_id)->get();
      $exames = Exame::where('empresa_id', $empresa_id)->get();

      $item->datahora_consulta = date('Y-m-d h:i', strtotime($item->datahora_consulta));

      return view('petshop.animais.consultas.edit', compact('item', 'animais', 'diagnosticos', "exames"));
    }

    public function update(Request $request, $id)
    {
      $request->validate([
        'animal_id' => 'required',
        'diagnostico_id' => 'nullable',
        'exame_id' => 'required',
        'datahora_consulta' => 'required',
        'status' => 'required',
      ]);

      try {
        $item = Consulta::findOrFail($id);
        __validaObjetoEmpresa($item);

        $item->update([
            'animal_id' => $request->animal_id,
            'diagnostico_id' => $request->diagnostico_id,
            'exame_id' => $request->exame_id,
            'datahora_consulta' => $request->datahora_consulta,
            'status' => $request->status,
            'observacao' => $request->observacao,
        ]);

        session()->flash("flash_success", "Consulta atualizada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
      }

      return redirect()->route('animais.consultas.index');
    }

    public function destroy($id)
    {
      try {
        $item = Consulta::findOrFail($id);
        __validaObjetoEmpresa($item);

        $item->delete();

        session()->flash("flash_success", "Consulta excluída com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
      }

      return redirect()->route('animais.consultas.index');
    }

}
