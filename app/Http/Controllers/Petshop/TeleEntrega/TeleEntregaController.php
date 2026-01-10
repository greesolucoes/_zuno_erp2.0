<?php

namespace App\Http\Controllers\Petshop\TeleEntrega;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Petshop\TeleEntrega;
use App\Models\Petshop\TipoTeleEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeleEntregaController extends Controller
{
    public function index(Request $request)
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $data = TeleEntrega::where('empresa_id', $empresa_id)
      ->when(!empty($request->cliente_id), function ($query) use ($request) {
        $query->where('cliente_id', $request->cliente_id);
      })
      ->when(!empty($request->status), function ($query) use ($request) {
        $query->where('status', $request->status);
      })
      ->paginate(env("PAGINACAO"));

      $clientes = Cliente::where('empresa_id', $empresa_id)->get();
      $status = TeleEntrega::status();

      // $tipos = TipoTeleEntrega::where('empresa_id', $empresa_id)->get();
      // return response()->json($tipos);

      return view('tele_entregas.index', compact('data', 'clientes', 'status'));	
    }

    public function show(Request $request, $id)
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $clientes = Cliente::where('empresa_id', $empresa_id)->get();
      $tipos = TipoTeleEntrega::where('empresa_id', $empresa_id)->get();

      $item = TeleEntrega::findOrFail($id);
      // return \response()->json($item);

      return view('tele_entregas.show', compact('clientes', 'tipos', 'item'));
    }
    
    public function preStore(Request $request){
        try{
            $cliente = null;
            if($request->cliente_id == null){
                $cliente = Cliente::create([
                    'empresa_id' => $request->empresa_id,
                    'razao_social' => $request->cliente_nome,
                    'telefone' => $request->cliente_fone,
                ]);
            }else{
                $cliente = Cliente::findOrFail($request->cliente_id);
            }
            // return \response()->json($cliente);

            $pedido = TeleEntrega::create([
                'empresa_id' => $request->empresa_id,
                'cliente_id' => $cliente->id,
                'valor' => 0,
                'status' => 'pendente',
                'datahora_entrega' => date('H:i'),
                'tipo_id' => 1,
                'rua' => $cliente->rua ?? 'afasfsdf', // Dado provisório
                'bairro' => $cliente->bairro ?? 'abadia', // Dado provisório
                'cidade_id' => 839, // Dado provisório
                'cep' => $cliente->cep ?? '12313', // Dado provisório
                'complemento' => $cliente->complemento ?? null,
                'motorista_nome' => null,
            ]);
            session()->flash("flash_success", "Pedido criado!");
            return redirect()->route('tele_entregas.show', ['id' =>$pedido->id]);
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado...', );
            return \response()->json($e->getMessage());

            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $request->validate([
        'cliente_id' => 'required',
        'tipo_id' => 'required',
        'datahora_entrega' => 'required',
        'valor' => 'required',
        'observacao' => 'nullable',
        'rua' => 'required',
        'numero' => 'required',
        'bairro' => 'required',
        'cidade_id' => 'required',
        'cep' => 'required',
        'complemento' => 'nullable',
        'motorista_nome' => 'nullable',
      ]);

      try {
        TeleEntrega::create([
          'cliente_id' => $request->cliente_id,
          'tipo_id' => $request->tipo_id,
          'datahora_entrega' => $request->datahora_entrega,
          'valor' => number_format((float)$request->valor, 2, '.', ''),
          'observacao' => $request->observacao,
          'rua' => $request->rua,
          'numero' => $request->numero,
          'bairro' => $request->bairro,
          'cidade_id' => $request->cidade_id,
          'cep' => $request->cep,
          'complemento' => $request->complemento,
          'motorista_nome' => $request->motorista_nome,
          'empresa_id' => $empresa_id,
        ]);

        session()->flash("flash_success", "Tele-entrega cadastrada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
      }

      return redirect()->route('tele_entregas.index');
    }

    public function edit($id)
    {
      $empresa_id = Auth::user()?->empresa?->empresa_id;

      $item = TeleEntrega::findOrFail($id);
      __validaObjetoEmpresa($item);

      $clientes = Cliente::where('empresa_id', $empresa_id)->get();
      $tipos = TipoTeleEntrega::where('empresa_id', $empresa_id)->get();

      $item->foi_pago = $item->foi_pago == 1 ? 'S' : 'N';

      return view('tele_entregas.edit', compact('item', 'clientes', 'tipos'));
    }

    public function update(Request $request, $id)
    {
      $request->validate([
        'cliente_id' => 'required',
        'tipo_id' => 'required',
        'datahora_entrega' => 'required',
        'valor' => 'required',
        'observacao' => 'nullable',
        'rua' => 'required',
        'numero' => 'required',
        'bairro' => 'required',
        'cidade_id' => 'required',
        'cep' => 'required',
        'complemento' => 'nullable',
        'motorista_nome' => 'nullable',
      ]);

      try {
        $item = TeleEntrega::findOrFail($id);
        __validaObjetoEmpresa($item);

        $valor_formatado = str_replace(',', '.', str_replace('.', '', $request->valor));

        $item->update([
          'cliente_id' => $request->cliente_id,
          'tipo_id' => $request->tipo_id,
          'datahora_entrega' => $request->datahora_entrega,
          'valor' => $valor_formatado,
          'observacao' => $request->observacao,
          'rua' => $request->rua,
          'numero' => $request->numero,
          'bairro' => $request->bairro,
          'cidade_id' => $request->cidade_id,
          'cep' => $request->cep,
          'complemento' => $request->complemento,
          'motorista_nome' => $request->motorista_nome,
          'status' => $request->status,
          'foi_pago' => $request->foi_pago == 'S' ? 1 : 0,
        ]);

        session()->flash("flash_success", "Tele-entrega atualizada com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
      }

      return redirect()->route('tele_entregas.index');
    }

    public function destroy($id)
    {
      try {
        $item = TeleEntrega::findOrFail($id);
        __validaObjetoEmpresa($item);

        $item->delete();

        session()->flash("flash_success", "Tele-entrega excluída com sucesso!");
      } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
      }

      return redirect()->route('tele_entregas.index');
    }

}
