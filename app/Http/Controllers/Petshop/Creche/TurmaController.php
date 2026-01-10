<?php

namespace App\Http\Controllers\Petshop\Creche;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\Petshop\Turma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Petshop\Creche;
use Illuminate\Support\Facades\DB;


class TurmaController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;
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

        return view('turmas.index', compact('data'));
    }

    public function create()
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;
        $funcionarios = Funcionario::where('empresa_id', $empresa_id)->get();
        $turma = new Turma();
        return view('turmas.create', compact('funcionarios', 'turma'));
    }

    public function store(Request $request)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;

        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'tipo' => 'required|string|in:pequeno,grande,individual,coletivo',
            'capacidade' => 'required|integer|min:1',
            'status' => 'required|string|in:disponivel,ocupado,em_limpeza,manutencao',
            'colaborador_id' => 'nullable|exists:funcionarios,id',
        ]);

        try {
            Turma::create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'tipo' => $request->tipo,
                'capacidade' => $request->capacidade,
                'status' => $request->status,
                'colaborador_id' => $request->colaborador_id,
                'empresa_id' => $empresa_id,
            ]);

            session()->flash('flash_success', 'Turma cadastrada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('turmas.index');
    }

    public function show(string $id)
    {
        return null;
    }

    public function edit(string $id)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;
        $turma = Turma::where('empresa_id', $empresa_id)->findOrFail($id);
        $funcionarios = Funcionario::where('empresa_id', $empresa_id)->get();
        $reservasAtivas = Creche::where('turma_id', $turma->id)
            ->whereIn(DB::raw('LOWER(estado)'), ['agendado', 'em_andamento'])
            ->whereDate('data_saida', '>=', now())
            ->count();

        return view('turmas.edit', compact('turma', 'funcionarios', 'reservasAtivas'));
    }

    public function update(Request $request, string $id)
    {
        $empresa_id = Auth::user()?->empresa?->empresa_id;

        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'tipo' => 'required|string|in:pequeno,grande,individual,coletivo',
            'capacidade' => 'required|integer|min:1',
            'status' => 'required|string|in:disponivel,ocupado,em_limpeza,manutencao',
            'colaborador_id' => 'nullable|exists:funcionarios,id',
        ]);

        try {
            $turma = Turma::where('empresa_id', $empresa_id)->findOrFail($id);
            $reservasAtivas = Creche::where('turma_id', $turma->id)
                ->whereIn(DB::raw('LOWER(estado)'), ['agendado', 'em_andamento'])
                ->whereDate('data', '>=', now())
                ->count();

            if ($request->capacidade < $reservasAtivas) {
                session()->flash('flash_error', 'Não é possível reduzir a capacidade para menos do que o número de reservas já existentes.');
                return redirect()->back()->withInput();
            }

            $turma->update([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'tipo' => $request->tipo,
                'capacidade' => $request->capacidade,
                'status' => $request->status,
                'colaborador_id' => $request->colaborador_id,
                'empresa_id' => $empresa_id,
            ]);

            session()->flash('flash_success', 'Turma atualizada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Erro ao atualizar turma: ' . $e->getMessage());
        }

        return redirect()->route('turmas.index');
    }

    public function destroy(string $id)
    {
        try {
            $empresa_id = Auth::user()?->empresa?->empresa_id;
            $turma = Turma::where('empresa_id', $empresa_id)->findOrFail($id);
            $turma->delete();
            session()->flash('flash_success', 'Turma excluída com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Erro ao excluir turma: ' . $e->getMessage());
        }

        return redirect()->route('turmas.index');
    }
}
