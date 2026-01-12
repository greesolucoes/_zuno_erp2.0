<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\SalaInternacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SalasInternacaoController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = $this->getEmpresaId();

        $salas = SalaInternacao::query()
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

        return view('petshop.vet.salas_internacao.index', [
            'salas' => $salas,
            'tiposSala' => $this->tiposSala(),
            'statusSala' => $this->statusSala(),
        ]);
    }

    public function create(): View
    {
        return view('petshop.vet.salas_internacao.create', [
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
            SalaInternacao::create(array_merge($validated, [
                'empresa_id' => $empresaId,
            ]));

            session()->flash('flash_success', 'Sala de internação cadastrada com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível cadastrar a sala de internação no momento.');

            return back()->withInput();
        }

        return redirect()->route('vet.salas-internacao.index');
    }

    public function edit(SalaInternacao $salaInternacao): View
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($salaInternacao->empresa_id === $empresaId, 403);

        return view('petshop.vet.salas_internacao.edit', [
            'salaInternacao' => $salaInternacao,
            'tiposSala' => $this->tiposSala(),
            'statusSala' => $this->statusSala(),
        ]);
    }

    public function update(Request $request, SalaInternacao $salaInternacao): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($salaInternacao->empresa_id === $empresaId, 403);

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
            $salaInternacao->update($validated);

            session()->flash('flash_success', 'Sala de internação atualizada com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível atualizar a sala de internação no momento.');

            return back()->withInput();
        }

        return redirect()->route('vet.salas-internacao.index');
    }

    public function destroy(SalaInternacao $salaInternacao): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($salaInternacao->empresa_id === $empresaId, 403);

        try {
            $salaInternacao->delete();

            session()->flash('flash_success', 'Sala de internação removida com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível remover a sala de internação no momento.');
        }

        return redirect()->route('vet.salas-internacao.index');
    }

    private function tiposSala(): array
    {
        return [
            'internacao-geral' => 'Internação geral',
            'isolamento' => 'Isolamento',
            'terapia-intensiva' => 'Terapia intensiva',
            'pos-operatorio' => 'Pós-operatório',
            'recuperacao' => 'Sala de recuperação',
            'infectocontagioso' => 'Controle de infectocontagiosos',
            'neonatal' => 'Internação neonatal',
            'outro' => 'Outro',
        ];
    }

    private function statusSala(): array
    {
        return [
            'disponivel' => 'Disponível',
            'ocupada' => 'Ocupada',
            'reservada' => 'Reservada',
            'manutencao' => 'Em manutenção',
        ];
    }

    private function getEmpresaId(): int
    {
        $empresaId = Auth::user()?->empresa?->empresa_id;

        abort_unless($empresaId, 403, 'Empresa não encontrada para o usuário autenticado.');

        return (int) $empresaId;
    }
}