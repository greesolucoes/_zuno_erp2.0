<?php

namespace App\Http\Controllers\Petshop\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\Petshop\Quarto;
use Illuminate\Http\Request;
use App\Models\Petshop\Hotel;
use Illuminate\Support\Facades\DB;

class QuartoController extends Controller
{
    public function index(Request $request)
    {

        $empresaId = request()->empresa_id;
        $pesquisa = $request->input('pesquisa');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $start_capacidade = $request->input('start_capacidade');
        $end_capacidade = $request->input('end_capacidade');
        $status = $request->input('status');
        $tipo = $request->input('tipo');

        $data = Quarto::where('empresa_id', $empresaId)
        ->when($pesquisa, function ($q) use ($pesquisa) {
            $q->where('nome', 'like', "%$pesquisa%");
        })
        ->when($start_date, function ($q) use ($start_date) {
            $q->whereDate('created_at', '>=', $start_date);
        })
        ->when($end_date, function ($q) use ($end_date) {
            $q->whereDate('created_at', '<=', $end_date);
        })
        ->when($start_capacidade, function ($q) use ($start_capacidade) {
            $q->where('capacidade', '>=', $start_capacidade);
        })
        ->when($end_capacidade, function ($q) use ($end_capacidade) {
            $q->where('capacidade', '<=', $end_capacidade);
        })
        ->when($status, function ($q) use ($status) {
            $q->where('status', $status);
        })
        ->when($tipo, function ($q) use ($tipo) {
            $q->where('tipo', $tipo);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"))->appends($request->all());

        return view('quartos.index', compact('data'));
    }

    public function create()
    {
        $empresaId = request()->empresa_id;
        $funcionarios = Funcionario::where('empresa_id', $empresaId)->get();


        return view('quartos.create', compact('funcionarios'));
    }

    public function store(Request $request)
    {
        $empresaId = request()->empresa_id;
        $this->_validate($request);

        try {
            DB::transaction(function () use ($request, $empresaId) {
                Quarto::create([
                    'nome' => $request->nome,
                    'descricao' => $request->descricao,
                    'tipo' => $request->tipo,
                    'capacidade' => $request->capacidade,
                    'status' => $request->status,
                    'colaborador_id' => $request->colaborador_id,
                    'empresa_id' => $empresaId,
                ]);
            });

            session()->flash('flash_sucesso', 'Quarto cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('quartos.index');
    }

    public function show(string $id) {}

    public function edit(string $id)
    {
        $quarto = Quarto::findOrFail($id);
        __validaObjetoEmpresa($quarto);

        $empresaId = request()->empresa_id;
        $funcionarios = Funcionario::where('empresa_id', $empresaId)->get();
        $reservasAtivas = Hotel::where('quarto_id', $quarto->id)
            ->whereIn(DB::raw('LOWER(estado)'), ['agendado', 'em_andamento'])
            ->whereDate('checkout', '>=', now())
            ->count();

        return view('quartos.edit', compact('quarto', 'funcionarios', 'reservasAtivas'));
    }


    public function update(Request $request, string $id)
    {
        $empresaId = request()->empresa_id;
        $this->_validate($request);

        try {
            $quarto = Quarto::where('empresa_id', $empresaId)->findOrFail($id);
            $reservasAtivas = Hotel::where('quarto_id', $quarto->id)
                ->whereIn(DB::raw('LOWER(estado)'), ['agendado', 'em_andamento'])
                ->whereDate('checkout', '>=', now())
                ->count();

            if ($request->capacidade < $reservasAtivas) {
                session()->flash('flash_erro', 'Não é possível reduzir a capacidade para menos do que o número de reservas já existentes.');
                return redirect()->back()->withInput();
            }

            DB::transaction(function () use ($request, $quarto) {
                $quarto->update([
                    'nome' => $request->nome,
                    'descricao' => $request->descricao,
                    'tipo' => $request->tipo,
                    'capacidade' => $request->capacidade,
                    'status' => $request->status,
                    'colaborador_id' => $request->colaborador_id,
                ]);
            });

            session()->flash('flash_sucesso', 'Quarto atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao atualizar quarto: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('quartos.index');
    }


    public function destroy(string $id)
    {
        try {

            $empresaId = request()->empresa_id;

            $quarto = Quarto::where('empresa_id', $empresaId)->findOrFail($id);
            __validaObjetoEmpresa($quarto);

            DB::transaction(function () use ($quarto) {
                $quarto->delete();
            });

            session()->flash('flash_sucesso', 'Quarto excluído com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao excluir quarto: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('quartos.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'tipo' => 'required|string|in:pequeno,grande,individual,coletivo',
            'capacidade' => 'required|integer|min:1',
            'status' => 'required|string|in:disponivel,em_limpeza,manutencao,em_uso,reservado,bloqueado',
            'colaborador_id' => 'nullable|exists:funcionarios,id',
        ];
        $messages = [
            'nome.required' => 'O nome é obrigatório.',
        ];
        $this->validate($request, $rules, $messages);
    }
}
