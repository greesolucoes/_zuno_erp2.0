<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\SalaInternacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
            ->paginate(env("PAGINACAO"))
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

        try {
            $this->_validate($request);

            $validated = $request->only([
                'nome',
                'identificador',
                'tipo',
                'status',
                'capacidade',
                'equipamentos',
                'observacoes',
            ]);

            DB::transaction(function () use ($empresaId, $validated) {
                SalaInternacao::create(array_merge($validated, [
                    'empresa_id' => $empresaId,
                ]));
            });

            session()->flash("flash_sucesso", "Sala de internação cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);

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

        try {
            $this->_validate($request);

            $validated = $request->only([
                'nome',
                'identificador',
                'tipo',
                'status',
                'capacidade',
                'equipamentos',
                'observacoes',
            ]);

            DB::transaction(function () use ($salaInternacao, $validated) {
                $salaInternacao->update($validated);
            });

            session()->flash("flash_sucesso", "Sala de internação atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);

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

            session()->flash("flash_sucesso", "Sala de internação removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('vet.salas-internacao.index');
    }

    private function _validate(Request $request): void
    {
        $rules = [
            'nome' => ['required', 'string', 'max:255'],
            'identificador' => ['nullable', 'string', 'max:50'],
            'tipo' => ['required', Rule::in(array_keys($this->tiposSala()))],
            'status' => ['required', Rule::in(array_keys($this->statusSala()))],
            'capacidade' => ['nullable', 'integer', 'min:1', 'max:999'],
            'equipamentos' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string'],
        ];

        $messages = [
            'nome.required' => 'O campo Nome é obrigatório.',
            'tipo.required' => 'O campo Tipo é obrigatório.',
            'status.required' => 'O campo Status é obrigatório.',
        ];

        $this->validate($request, $rules, $messages);
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
        $empresaId = request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;

        abort_unless($empresaId, 403, 'Empresa não encontrada para o usuário autenticado.');

        return (int) $empresaId;
    }
}
