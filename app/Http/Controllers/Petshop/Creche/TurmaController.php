<?php

namespace App\Http\Controllers\Petshop\Creche;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\Petshop\Turma;
use Illuminate\Http\Request;
use App\Models\Petshop\Creche;
use Illuminate\Support\Facades\DB;


class TurmaController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = request()->empresa_id;
        $pesquisa = $request->input('pesquisa');
        $startDate = $request->input('start_date');

        $query = Turma::where('empresa_id', $empresa_id);

        if ($pesquisa) {
            $query->where(function ($q) use ($pesquisa) {
                $q->where('nome', 'like', "%$pesquisa%")
                  ->orWhere('descricao', 'like', "%$pesquisa%");
            });
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        $data = $query->paginate(env("PAGINACAO"))->appends($request->all());

        return view('petshop.turmas.index', compact('data'));
    }

    public function create()
    {
        $empresa_id = request()->empresa_id;
        $funcionarios = Funcionario::where('empresa_id', $empresa_id)->get();
        $turma = new Turma();
        return view('petshop.turmas.create', compact('funcionarios', 'turma'));
    }

    public function store(Request $request)
    {
        $empresa_id = request()->empresa_id;
        $this->_validate($request);

        try {
            DB::transaction(function () use ($request, $empresa_id) {
                Turma::create([
                    'nome' => $request->nome,
                    'descricao' => $request->descricao,
                    'tipo' => $request->tipo,
                    'capacidade' => $request->capacidade,
                    'status' => $request->status,
                    'colaborador_id' => $request->colaborador_id,
                    'empresa_id' => $empresa_id,
                ]);
            });

            session()->flash('flash_sucesso', 'Turma cadastrada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('turmas.index');
    }

    public function show(string $id)
    {
        return null;
    }

    public function edit(string $id)
    {
        $empresa_id = request()->empresa_id;
        $turma = Turma::where('empresa_id', $empresa_id)->findOrFail($id);
        $funcionarios = Funcionario::where('empresa_id', $empresa_id)->get();
        $reservasAtivas = Creche::where('turma_id', $turma->id)
            ->whereIn(DB::raw('LOWER(estado)'), ['agendado', 'em_andamento'])
            ->whereDate('data_saida', '>=', now())
            ->count();

        return view('petshop.turmas.edit', compact('turma', 'funcionarios', 'reservasAtivas'));
    }

    public function update(Request $request, string $id)
    {
        $empresa_id = request()->empresa_id;
        $this->_validate($request);

        try {
            $turma = Turma::where('empresa_id', $empresa_id)->findOrFail($id);
            $reservasAtivas = Creche::where('turma_id', $turma->id)
                ->whereIn(DB::raw('LOWER(estado)'), ['agendado', 'em_andamento'])
                ->whereDate('data', '>=', now())
                ->count();

            if ($request->capacidade < $reservasAtivas) {
                session()->flash('flash_erro', 'Não é possível reduzir a capacidade para menos do que o número de reservas já existentes.');
                return redirect()->back()->withInput();
            }

            DB::transaction(function () use ($request, $turma) {
                $turma->update([
                    'nome' => $request->nome,
                    'descricao' => $request->descricao,
                    'tipo' => $request->tipo,
                    'capacidade' => $request->capacidade,
                    'status' => $request->status,
                    'colaborador_id' => $request->colaborador_id,
                ]);
            });

            session()->flash('flash_sucesso', 'Turma atualizada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao atualizar turma: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('turmas.index');
    }

    public function destroy(string $id)
    {
        try {
            $empresa_id = request()->empresa_id;
            $turma = Turma::where('empresa_id', $empresa_id)->findOrFail($id);
            DB::transaction(function () use ($turma) {
                $turma->delete();
            });
            session()->flash('flash_sucesso', 'Turma excluída com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao excluir turma: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('turmas.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'tipo' => 'required|string|in:pequeno,grande,individual,coletivo',
            'capacidade' => 'required|integer|min:1',
            'status' => 'required|string|in:disponivel,ocupado,em_limpeza,manutencao',
            'colaborador_id' => 'nullable|exists:funcionarios,id',
        ];
        $messages = [
            'nome.required' => 'O nome é obrigatório.',
        ];
        $this->validate($request, $rules, $messages);
    }
}
