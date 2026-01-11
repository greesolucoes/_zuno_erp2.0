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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TurmaEventoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
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

        return view('petshop.turmas.eventos.index', compact(
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
        $empresaId = request()->empresa_id;
        $turmas = Turma::where('empresa_id', $empresaId)->get();
        $servicos = Servico::with(['funcionario', 'fornecedor'])
            ->where('empresa_id', $empresaId)
            ->get();

        $turmaId = $request->get('turma_id');

        return view('petshop.turmas.eventos.create', compact('turmas', 'servicos', 'turmaId'));
    }

    public function edit(Request $request, $id)
    {
        $item = TurmaEvento::findOrFail($id);

        $empresaId = request()->empresa_id;
        $turmas = Turma::where('empresa_id', $empresaId)->get();
        $servicos = Servico::with(['funcionario', 'fornecedor'])
            ->where('empresa_id', $empresaId)
            ->get();

        $turmaId = $request->get('turma_id');

        return view('petshop.turmas.eventos.edit', compact('turmas', 'servicos', 'turmaId', 'item'));
    }

    public function store(Request $request)
    {
        $data = $this->_validate($request);

        $turma = Turma::findOrFail($data['turma_id']);
        if ($turma->status !== Turma::STATUS_DISPONIVEL) {
            session()->flash('flash_erro', 'Turma indisponível para agendamentos.');
            return back()->withInput();
        }

        $servico = Servico::find($data['servico_id']);

        if ($servico?->tipo_servico == 2) {
            if (!isset($servico->fornecedor_id) || $servico->fornecedor_id == 0) {
                session()->flash('flash_erro', 'Serviço não possui um fornecedor associado!');
                return back()->withInput();
            }

            $data['fornecedor_id'] = $servico->fornecedor_id;
        } else {
            if (!isset($servico->funcionario_id) || $servico->funcionario_id == 0) {
                session()->flash('flash_erro', 'Serviço não possui um colaborador associado!');
                return back()->withInput();
            }

            $data['prestador_id'] = $servico->funcionario_id;
        }

        try {
            DB::transaction(function () use ($data) {
                TurmaEvento::create($data);
            });

            session()->flash('flash_sucesso', 'Evento registrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
            return back()->withInput();
        }

        return redirect()->route('turmas.eventos.index', ['turma_id' => $data['turma_id']]);
    }

    public function update(Request $request, $id)
    {
        $data = $this->_validate($request);

        $turma = Turma::findOrFail($data['turma_id']);
        if ($turma->status !== Turma::STATUS_DISPONIVEL) {
            session()->flash('flash_erro', 'Turma indisponível para agendamentos.');
            return back()->withInput();
        }

        try {
            $item = TurmaEvento::findOrFail($id);

            $servico = Servico::find($data['servico_id']);

            if ($servico?->tipo_servico == 2) {
                if (!isset($servico->fornecedor_id) || $servico->fornecedor_id == 0) {
                    session()->flash('flash_erro', 'Serviço não possui um fornecedor associado!');
                    return back()->withInput();
                }

                $data['fornecedor_id'] = $servico->fornecedor_id;
            } else {
                if (!isset($servico->funcionario_id) || $servico->funcionario_id == 0) {
                    session()->flash('flash_erro', 'Serviço não possui um colaborador associado!');
                    return back()->withInput();
                }

                $data['prestador_id'] = $servico->funcionario_id;
            }

            DB::transaction(function () use ($item, $data) {
                $item->update($data);
            });
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Turma Evento', 'erro', $e->getMessage());
            LogService::logMessage($e->getMessage(), 'ERROR');
            __saveLogError($e, request()->empresa_id);

            session()->flash('flash_erro', 'Erro ao atualizar evento: ' . $e->getMessage());
            return redirect()->route('turmas.eventos.index', ['turma_id' => $data['turma_id']]);
        }

        session()->flash('flash_sucesso', 'Evento atualizado com sucesso!');
        return redirect()->route('turmas.eventos.index', ['turma_id' => $data['turma_id']]);
    }

    public function finalizar(TurmaEvento $evento)
    {
        try {
            DB::transaction(function () use ($evento) {
                $evento->update([
                    'fim' => Carbon::now(),
                ]);

                if ($evento->servico && $evento->servico->tipo_servico == 2) {
                    ContaPagar::create([
                        'empresa_id' => request()->empresa_id,
                        'fornecedor_id' => $evento->fornecedor_id,
                        'descricao' => 'Serviço ' . ($evento->servico->nome ?? '') . ' - Turma ' . ($evento->turma->nome ?? ''),
                        'valor_integral' => __convert_value_bd($evento->servico->valor ?? 0),
                        'data_vencimento' => Carbon::now()->toDateString(),
                        'tipo_pagamento' => '99',
                        'status' => 0,
                        'local_id' => __getLocalAtivo()->id ?? null,
                    ]);
                }
            });

            session()->flash('flash_sucesso', 'Serviço finalizado!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao finalizar serviço: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return back();
    }

    public function destroy($id)
    {
        try {
            $evento = TurmaEvento::findOrFail($id);

            DB::transaction(function () use ($evento) {
                $evento->delete();
            });

            session()->flash('flash_sucesso', 'Evento excluido com sucesso!');
        } catch (\Exception $e) {
            __createLog(\request()->empresa_id, 'Evento de Turma', 'erro', $e->getMessage());
            LogService::logMessage($e->getMessage(), 'ERROR');
            __saveLogError($e, request()->empresa_id);

            session()->flash('flash_erro', 'Erro ao excluir evento: ' . $e->getMessage());
        }

        return back();
    }

    private function _validate(Request $request): array
    {
        $rules = [
            'turma_id' => 'required|exists:petshop_turmas,id',
            'servico_id' => 'nullable|exists:servicos,id',
            'inicio' => 'required|date',
            'fim' => 'nullable|date|after_or_equal:inicio',
            'descricao' => 'nullable|string',
        ];
        $messages = [];

        $this->validate($request, $rules, $messages);

        return $request->only(array_keys($rules));
    }
}
