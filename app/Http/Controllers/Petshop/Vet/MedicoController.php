<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\Petshop\Medico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class MedicoController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = $this->getEmpresaId();

        $medicos = Medico::with(['funcionario.cargo'])
            ->where('empresa_id', $empresaId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->toString();

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('crmv', 'like', "%{$term}%")
                        ->orWhere('especialidade', 'like', "%{$term}%")
                        ->orWhereHas('funcionario', function ($funcionarioQuery) use ($term) {
                            $funcionarioQuery->where('nome', 'like', "%{$term}%");
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->all());

        return view('petshop.vet.medicos.index', [
            'medicos' => $medicos,
        ]);
    }

    public function create(): View
    {
        $empresaId = $this->getEmpresaId();

        $occupiedEmployeeIds = Medico::where('empresa_id', $empresaId)
            ->pluck('funcionario_id');

        $employees = Funcionario::query()
            ->where('empresa_id', $empresaId)
            ->whereNotIn('id', $occupiedEmployeeIds)
            ->with('cargo')
            ->orderBy('nome')
            ->get();

        return view('petshop.vet.medicos.create', [
            'employees' => $employees,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        $validated = $request->validate([
            'funcionario_id' => [
                'required',
                'exists:funcionarios,id',
                Rule::unique('petshop_medicos', 'funcionario_id'),
            ],
            'crmv' => [
                'required',
                'string',
                'max:30',
                Rule::unique('petshop_medicos', 'crmv')
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'especialidade' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'observacoes' => ['nullable', 'string'],
            'status' => ['required', 'in:ativo,inativo'],
        ], [
            'funcionario_id.required' => 'Selecione um colaborador para associar ao médico.',
            'funcionario_id.unique' => 'Este colaborador já está cadastrado como médico.',
            'crmv.unique' => 'Já existe um médico com este CRMV cadastrado.',
        ]);

        try {
            Medico::create(array_merge($validated, [
                'empresa_id' => $empresaId,
            ]));

            session()->flash('flash_success', 'Médico cadastrado com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível cadastrar o médico no momento.');

            return back()->withInput();
        }

        return redirect()->route('vet.medicos.index');
    }

    public function edit(Medico $medico): View
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($medico->empresa_id === $empresaId, 403);

        $occupiedEmployeeIds = Medico::where('empresa_id', $empresaId)
            ->where('id', '!=', $medico->id)
            ->pluck('funcionario_id');

        $employees = Funcionario::query()
            ->where('empresa_id', $empresaId)
            ->whereNotIn('id', $occupiedEmployeeIds)
            ->with('cargo')
            ->orderBy('nome')
            ->get();

        return view('petshop.vet.medicos.edit', [
            'medico' => $medico,
            'employees' => $employees,
        ]);
    }

    public function update(Request $request, Medico $medico): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($medico->empresa_id === $empresaId, 403);

        $validated = $request->validate([
            'funcionario_id' => [
                'required',
                'exists:funcionarios,id',
                Rule::unique('petshop_medicos', 'funcionario_id')->ignore($medico->id),
            ],
            'crmv' => [
                'required',
                'string',
                'max:30',
                Rule::unique('petshop_medicos', 'crmv')
                    ->ignore($medico->id)
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'especialidade' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'observacoes' => ['nullable', 'string'],
            'status' => ['required', 'in:ativo,inativo'],
        ], [
            'crmv.unique' => 'Já existe um médico com este CRMV cadastrado.',
        ]);

        try {
            $medico->update($validated);

            session()->flash('flash_success', 'Médico atualizado com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível atualizar o médico no momento.');

            return back()->withInput();
        }

        return redirect()->route('vet.medicos.index');
    }

    public function destroy(Medico $medico): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($medico->empresa_id === $empresaId, 403);

        try {
            $medico->delete();

            session()->flash('flash_success', 'Médico removido com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível remover o médico no momento.');
        }

        return redirect()->route('vet.medicos.index');
    }

    private function getEmpresaId(): int
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;

        abort_unless($empresaId, 403, 'Empresa não encontrada para o usuário autenticado.');

        return (int) $empresaId;
    }
}