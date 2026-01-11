<?php

namespace App\Http\Controllers\Petshop\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Quarto;
use App\Models\Petshop\QuartoEvento;
use App\Models\Servico;
use App\Models\ContaPagar;
use App\Models\Fornecedor;
use App\Models\Funcionario;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class QuartoEventoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $quartos = Quarto::where('empresa_id', $empresaId)->get();
        $servicos = Servico::where('empresa_id', $empresaId)->get();
        $prestadores_servico = Funcionario::where('empresa_id', $empresaId)->get()
        ->concat(Fornecedor::where('empresa_id', $empresaId)->get());

        $quartoId = $request->get('quarto_id');
        $descricao = $request->get('descricao');
        $servico_id = $request->get('servico_id');
        $funcionario_id = $request->get('hidden_funcionario_id');
        $fornecedor_id = $request->get('hidden_fornecedor_id');

        $start_date = $request->get('start_date', $request->start_date);
        $end_date = $request->get('end_date');

        $eventos = collect();
        if ($quartoId) {
            $eventos = QuartoEvento::with(['servico', 'prestador', 'fornecedor'])
                ->where('quarto_id', $quartoId)
                ->when($descricao, function ($query) use ($descricao) {
                    $query->where('descricao', 'like', '%' . $descricao . '%');
                })
                ->when($start_date, function ($query) use ($start_date) {
                    $query->where('inicio', '>=', $start_date);
                })
                ->when($end_date, function ($query) use ($end_date) {
                    $query->where('fim', '<=', $end_date);
                })
                ->when($servico_id, function ($query) use ($servico_id) {
                    $query->where('servico_id', $servico_id);
                })
                ->when($funcionario_id, function ($query) use ($funcionario_id) {
                    $query->where('prestador_id', $funcionario_id);
                })
                ->when($fornecedor_id, function ($query) use ($fornecedor_id) {
                    $query->where('fornecedor_id', $fornecedor_id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('petshop.quartos.eventos.index', compact(
            'quartos',
            'eventos',
            'servicos',
            'prestadores_servico',
            'quartoId',
            'descricao',
            'funcionario_id',
            'fornecedor_id',
            'servico_id',
            'start_date',
            'end_date'
        ));
    }

    public function create(Request $request)
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $quartos = Quarto::where('empresa_id', $empresaId)->get();
        $servicos = Servico::with(['funcionario', 'fornecedor'])
            ->where('empresa_id', $empresaId)
            ->get();

        $quartoId = $request->get('quarto_id');

        return view('petshop.quartos.eventos.create', compact('quartos', 'servicos', 'quartoId'));
    }

    public function edit(Request $request, $id)
    {
        $item = QuartoEvento::findOrFail($id);
        $empresa_id = Auth::user()?->empresa?->empresa_id;
        $quartos = Quarto::where('empresa_id', $empresa_id)->get();
        $servicos = Servico::with(['funcionario', 'fornecedor'])
            ->where('empresa_id', $empresa_id)
            ->get();

        return view('petshop.quartos.eventos.edit', compact('quartos', 'servicos', 'item'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'quarto_id' => 'required|exists:quartos,id',
            'servico_id' => 'nullable|exists:servicos,id',
            'inicio' => 'required|date',
            'fim' => 'nullable|date|after_or_equal:inicio',
            'descricao' => 'nullable|string',
        ]);

        $quarto = Quarto::findOrFail($data['quarto_id']);
        if ($quarto->status !== Quarto::STATUS_DISPONIVEL) {
            return back()->with('flash_error', 'Quarto indisponível para agendamentos.')->withInput();
        }

        if (isset($request->servico_id)) {
            $servico = Servico::find($data['servico_id']);
            
            if ($servico?->tipo_servico == 2) {
                if (!isset($servico->fornecedor_id)) {
                    return back()->with('flash_error', 'Serviço não possui um fornecedor associado!')->withInput();
                }

                $data['fornecedor_id'] = $servico->fornecedor_id;
            } else {
                if (!isset($servico->funcionario_id)) {
                    return back()->with('flash_error', 'Serviço não possui um colaborador associado!')->withInput();
                }

                $data['prestador_id'] = $servico->funcionario_id;
            }
        }

        QuartoEvento::create($data);

        return redirect()->route('quartos.eventos.index', ['quarto_id' => $data['quarto_id']])->with('flash_success', 'Evento registrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'quarto_id' => 'required|exists:quartos,id',
            'servico_id' => 'nullable|exists:servicos,id',
            'inicio' => 'required|date',
            'fim' => 'nullable|date|after_or_equal:inicio',
            'descricao' => 'nullable|string',
        ]);

        $quarto = Quarto::findOrFail($data['quarto_id']);
        if ($quarto->status !== Quarto::STATUS_DISPONIVEL) {
            return back()->with('flash_error', 'Quarto indisponível para agendamentos.')->withInput();
        }

        try {
            $evento = QuartoEvento::findOrFail($id);

            $servico = Servico::find($data['servico_id']);
            
            if ($servico?->tipo_servico == 2) {
                if (!isset($servico->fornecedor_id) || $servico->fornecedor_id == 0) {
                    return back()->with('flash_error', 'Serviço não possui um fornecedor associado!')->withInput();
                }

                $data['fornecedor_id'] = $servico->fornecedor_id;
            } else {
                if (!isset($servico->funcionario_id) || $servico->funcionario_id == 0) {
                    return back()->with('flash_error', 'Serviço não possui um colaborador associado!')->withInput();
                }

                $data['prestador_id'] = $servico->funcionario_id;
            }

            $evento->update($data);
        } catch (\Exception $e) {
            __createLog(\request()->empresa_id, 'Evento de Quarto', 'erro', $e->getMessage());
            LogService::logMessage($e->getMessage(), 'ERROR');

            return redirect()->route('quartos.eventos.index', ['quarto_id' => $data['quarto_id']])->with('flash_error', 'Erro ao atualizar o evento...');
        }


        return redirect()->route('quartos.eventos.index', ['quarto_id' => $data['quarto_id']])->with('flash_success', 'Evento atualizado com sucesso!');
    }

    public function finalizar(QuartoEvento $evento)
    {
        $evento->update([
            'fim' => Carbon::now(),
        ]);

        if ($evento->servico && $evento->servico->tipo_servico == 2) {
            ContaPagar::create([
                'empresa_id' => Auth::user()?->empresa?->empresa_id,
                'fornecedor_id' => $evento->fornecedor_id,
                'descricao' => 'Serviço ' . ($evento->servico->nome ?? '') . ' - Quarto ' . ($evento->quarto->nome ?? ''),
                'valor_integral' => __convert_value_bd($evento->servico->valor ?? 0),
                'data_vencimento' => Carbon::now()->toDateString(),
                'tipo_pagamento' => '99',
                'status' => 0,
                'local_id' => __getLocalAtivo()->id ?? null,
            ]);
        }

        return back()->with('flash_success', 'Serviço finalizado!');
    }

    public function destroy($id)
    {
        try {
            $evento = QuartoEvento::findOrFail($id);

            $evento->delete();

            return back()->with('flash_success', 'Evento excluido com sucesso!');
        } catch (\Exception $e) {
            __createLog(\request()->empresa_id, 'Evento de Quarto', 'erro', $e->getMessage());
            LogService::logMessage($e->getMessage(), 'ERROR');

            return back()->with('flash_error', 'Erro ao excluir evento...');
        }
    }

}
