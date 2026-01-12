<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\SalaAtendimento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SalasAtendimentoController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = $this->getEmpresaId();

        $salas = SalaAtendimento::query()
            ->where('empresa_id', $empresaId)
            ->when($request->filled('busca'), function ($query) use ($request) {
                $termo = $request->string('busca')->toString();

                $query->where(function ($subQuery) use ($termo) {
                    $subQuery->where('nome', 'like', "%{$termo}%")
                        ->orWhere('identificador', 'like', "%{$termo}%")
                        ->orWhere('tipo', 'like', "%{$termo}%")
                        ->orWhere('equipamentos', 'like', "%{$termo}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('tipo'), function ($query) use ($request) {
                $query->where('tipo', $request->string('tipo')->toString());
            })
            ->orderBy('nome')
            ->paginate(10)
            ->appends($request->all());

        return view('petshop.vet.salas_atendimento.index', [
            'salas' => $salas,
            'tiposSala' => $this->tiposSala(),
            'statusSala' => $this->statusSala(),
        ]);
    }

    public function create(): View
    {
        return view('petshop.vet.salas_atendimento.create', [
            'tiposSala' => $this->tiposSala(),
            'statusSala' => $this->statusSala(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'identificador' => ['nullable', 'string', 'max:50'],
            'tipo' => ['required', Rule::in(array_keys($this->tiposSala()))],
            'status' => ['required', Rule::in(array_keys($this->statusSala()))],
            'capacidade' => ['nullable', 'integer', 'min:1', 'max:999'],
            'equipamentos' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string'],
        ]);

        try {
            SalaAtendimento::create(array_merge($validated, [
                'empresa_id' => $empresaId,
            ]));

            session()->flash('flash_success', 'Sala de atendimento cadastrada com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível cadastrar a sala de atendimento no momento.');

            return back()->withInput();
        }

        return redirect()->route('vet.salas-atendimento.index');
    }

    public function edit(SalaAtendimento $salaAtendimento): View
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($salaAtendimento->empresa_id === $empresaId, 403);

        return view('petshop.vet.salas_atendimento.edit', [
            'salaAtendimento' => $salaAtendimento,
            'tiposSala' => $this->tiposSala(),
            'statusSala' => $this->statusSala(),
        ]);
    }

    public function update(Request $request, SalaAtendimento $salaAtendimento): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($salaAtendimento->empresa_id === $empresaId, 403);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'identificador' => ['nullable', 'string', 'max:50'],
            'tipo' => ['required', Rule::in(array_keys($this->tiposSala()))],
            'status' => ['required', Rule::in(array_keys($this->statusSala()))],
            'capacidade' => ['nullable', 'integer', 'min:1', 'max:999'],
            'equipamentos' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string'],
        ]);

        try {
            $salaAtendimento->update($validated);

            session()->flash('flash_success', 'Sala de atendimento atualizada com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível atualizar a sala de atendimento no momento.');

            return back()->withInput();
        }

        return redirect()->route('vet.salas-atendimento.index');
    }

    public function destroy(SalaAtendimento $salaAtendimento): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($salaAtendimento->empresa_id === $empresaId, 403);

        try {
            $salaAtendimento->delete();

            session()->flash('flash_success', 'Sala de atendimento removida com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível remover a sala de atendimento no momento.');
        }

        return redirect()->route('vet.salas-atendimento.index');
    }

    private function tiposSala(): array
    {
        return [
            'consultorio' => 'Consultório',
            'triagem' => 'Sala de triagem',
            'vacinacao' => 'Sala de vacinação',
            'emergencia' => 'Sala de emergência',
            'laboratorio' => 'Laboratório',
            'outro' => 'Outro',
        ];
    }

    private function statusSala(): array
    {
        return [
            'disponivel' => 'Disponível',
            'manutencao' => 'Em manutenção',
            'indisponivel' => 'Indisponível',
        ];
    }

    private function getEmpresaId(): int
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;

        abort_unless($empresaId, 403, 'Empresa não encontrada para o usuário autenticado.');

        return (int) $empresaId;
    }
}