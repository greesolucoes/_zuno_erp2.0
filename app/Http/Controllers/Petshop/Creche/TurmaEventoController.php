<?php

namespace App\Http\Controllers\Petshop\Creche;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Turma;
use App\Models\Petshop\TurmaEvento;
use App\Models\Servico;
use App\Models\ContaPagar;
use App\Models\Fornecedor;
use App\Models\Funcionario;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class TurmaEventoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $turmas = Turma::where('empresa_id', $empresaId)->get();
        $servicos = Servico::where('empresa_id', $empresaId)
            ->get();
        $prestadores_servico = Funcionario::where('empresa_id', $empresaId)->get()
        ->concat(Fornecedor::where('empresa_id', $empresaId)->get());

        $turmaId = $request->get('turma_id');
        $descricao = $request->get('descricao');
        $servico_id = $request->get('servico_id');
        $funcionario_id = $request->get('hidden_funcionario_id');
        $fornecedor_id = $request->get('hidden_fornecedor_id');

        $start_date = $request->get('start_date', $request->start_date);
        $end_date = $request->get('end_date');

        $eventos = collect();
        if ($turmaId) {
            $eventos = TurmaEvento::with(['servico', 'prestador', 'fornecedor'])
                ->where('turma_id', $turmaId)
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
                ->orderBy('inicio')
                ->get();
        }

        return view('turmas.eventos.index', compact(
            'turmas',
            'eventos',
            'servicos',
            'prestadores_servico',
            'turmaId',
            'descricao',
            'servico_id',
            'funcionario_id',
            'fornecedor_id',
            'start_date',
            'end_date'
        ));
    }

    public function create(Request $request)
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;
        $turmas = Turma::where('empresa_id', $empresaId)->get();
        $servicos = Servico::with(['funcionario', 'fornecedor'])
            ->where('empresa_id', $empresaId)
            ->get();

        $turmaId = $request->get('turma_id');

        return view('turmas.eventos.create', compact('turmas', 'servicos', 'turmaId'));
    }

    public function edit(Request $request, $id)
    {
        $item = TurmaEvento::findOrFail($id);

        $empresaId = Auth::user()?->empresa?->empresa_id;
        $turmas = Turma::where('empresa_id', $empresaId)->get();
        $servicos = Servico::with(['funcionario', 'fornecedor'])
            ->where('empresa_id', $empresaId)
            ->get();

        $turmaId = $request->get('turma_id');

        return view('turmas.eventos.edit', compact('turmas', 'servicos', 'turmaId', 'item'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'servico_id' => 'nullable|exists:servicos,id',
            'inicio' => 'required|date',
            'fim' => 'nullable|date|after_or_equal:inicio',
            'descricao' => 'nullable|string',
        ]);

        $turma = Turma::findOrFail($data['turma_id']);
        if ($turma->status !== Turma::STATUS_DISPONIVEL) {
            return back()->with('flash_error', 'Turma indisponível para agendamentos.')->withInput();
        }

        $servico = Servico::find($data['servico_id']);

        if ($servico?->tipo_servico == 2) {
            if (!isset($servico->fornecedor_id) || $servico->fornecedor_id == 0) {
                return back()->with('flash_error', 'Serviço não possui um fornecedor associado!')->withInput();
            }

            $data['fornecedor_id'] = $servico->fornecedor_id;
        } else {
            if (!isset($servico->funcionario_id) || $servico->funcionario_id == 0) {
                return back()->with('flash_error', 'Serviço não possui um colaborador associado!')->withInput();
            }

            $data['prestador_id'] = $servico->funcionario_id;
        }

        TurmaEvento::create($data);

        return redirect()->route('turmas.eventos.index', ['turma_id' => $data['turma_id']])->with('flash_success', 'Evento registrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'servico_id' => 'nullable|exists:servicos,id',
            'inicio' => 'required|date',
            'fim' => 'nullable|date|after_or_equal:inicio',
            'descricao' => 'nullable|string',
        ]);

        $turma = Turma::findOrFail($data['turma_id']);
        if ($turma->status !== Turma::STATUS_DISPONIVEL) {
            return back()->with('flash_error', 'Turma indisponível para agendamentos.')->withInput();
        }

        try {
            $item = TurmaEvento::findOrFail($id);

            $servico = Servico::find($data['servico_id']);

            if ($servico?->tipo_servico == 2) {
                if (!isset($servico->fornecedor_id) || $servico->fornecedor_id == 0) {
                    return back()->with('flash_error', 'Serviço não possui um fornecedor associado!')->withInput();
                }

                $data['fornecedor_id'] = $servico->fornecedor_id;
            } else {
                if (!isset($servico->funcionario_id) || $servico->funcionario_id == 0) {
                    return back()->with('flash_error', 'Serviço não possui um colaborador associado!')->withInput();
                }

                $data['prestador_id'] = $servico->funcionario_id;
            }

            $item->update($data);
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Turma Evento', 'erro', $e->getMessage());
            LogService::logMessage($e->getMessage(), 'ERROR');

            return redirect()->route('turmas.eventos.index', ['turma_id' => $data['turma_id']])->with('flash_error', 'Erro ao atualizar evento...');
        }

        return redirect()->route('turmas.eventos.index', ['turma_id' => $data['turma_id']])->with('flash_success', 'Evento atualizado com sucesso!');
    }

    public function finalizar(TurmaEvento $evento)
    {
        $evento->update([
            'fim' => Carbon::now(),
        ]);

        if ($evento->servico && $evento->servico->tipo_servico == 2) {
            ContaPagar::create([
                'empresa_id' => Auth::user()?->empresa?->empresa_id,
                'fornecedor_id' => $evento->fornecedor_id,
                'descricao' => 'Serviço ' . ($evento->servico->nome ?? '') . ' - Turma ' . ($evento->turma->nome ?? ''),
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
            $evento = TurmaEvento::findOrFail($id);

            $evento->delete();

            return back()->with('flash_success', 'Evento excluido com sucesso!');
        } catch (\Exception $e) {
            __createLog(\request()->empresa_id, 'Evento de Turma', 'erro', $e->getMessage());
            LogService::logMessage($e->getMessage(), 'ERROR');

            return back()->with('flash_error', 'Erro ao excluir evento...');
        }
    }
}