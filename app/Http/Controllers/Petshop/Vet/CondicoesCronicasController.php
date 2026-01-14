<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\CondicaoCronica;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CondicoesCronicasController extends Controller
{
    public function index(Request $request): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        $condicoes = CondicaoCronica::query()
            ->where('empresa_id', $empresaId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = Str::of((string) $request->input('search'))->trim()->toString();

                if ($term === '') {
                    return;
                }

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('nome', 'like', "%{$term}%")
                        ->orWhere('descricao', 'like', "%{$term}%")
                        ->orWhere('orientacoes', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = Str::of((string) $request->input('status'))->trim()->toString();

                if (array_key_exists($status, $this->statusOptions())) {
                    $query->where('status', $status);
                }
            })
            ->orderByDesc('updated_at')
            ->paginate(env("PAGINACAO"))
            ->appends($request->all());

        return view('petshop.vet.condicoes_cronicas.index', [
            'condicoes' => $condicoes,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create(): View|ViewFactory
    {
        return view('petshop.vet.condicoes_cronicas.create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        $this->_validate($request);

        $status = Str::of((string) $request->input('status'))->trim()->toString();

        if (! array_key_exists($status, $this->statusOptions())) {
            $status = 'ativo';
        }

        try {
            DB::transaction(function () use ($empresaId, $request, $status) {
                CondicaoCronica::create([
                    'empresa_id' => $empresaId,
                    'nome' => Str::of((string) $request->input('nome'))->trim()->toString(),
                    'descricao' => $this->normalizeNullableText($request->input('descricao')),
                    'orientacoes' => $this->normalizeNullableText($request->input('orientacoes')),
                    'status' => $status,
                ]);
            });

            session()->flash("flash_sucesso", "Condição crônica cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);

            return back()->withInput();
        }

        return redirect()->route('vet.chronic-conditions.index');
    }

    public function edit(CondicaoCronica $condicaoCronica): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($condicaoCronica->empresa_id === $empresaId, 403);

        return view('petshop.vet.condicoes_cronicas.edit', [
            'condicaoCronica' => $condicaoCronica,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(Request $request, CondicaoCronica $condicaoCronica): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($condicaoCronica->empresa_id === $empresaId, 403);

        $this->_validate($request);

        $status = Str::of((string) $request->input('status'))->trim()->toString();

        if (! array_key_exists($status, $this->statusOptions())) {
            $status = $condicaoCronica->status ?? 'ativo';
        }

        try {
            DB::transaction(function () use ($condicaoCronica, $request, $status) {
                $condicaoCronica->update([
                    'nome' => Str::of((string) $request->input('nome'))->trim()->toString(),
                    'descricao' => $this->normalizeNullableText($request->input('descricao')),
                    'orientacoes' => $this->normalizeNullableText($request->input('orientacoes')),
                    'status' => $status,
                ]);
            });

            session()->flash("flash_sucesso", "Condição crônica atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);

            return back()->withInput();
        }

        return redirect()->route('vet.chronic-conditions.index');
    }

    public function destroy(CondicaoCronica $condicaoCronica): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($condicaoCronica->empresa_id === $empresaId, 403);

        try {
            $condicaoCronica->delete();

            session()->flash("flash_sucesso", "Condição crônica removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('vet.chronic-conditions.index');
    }

    private function _validate(Request $request): void
    {
        $rules = [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'orientacoes' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_keys($this->statusOptions()))],
        ];

        $messages = [
            'nome.required' => 'O campo Nome é obrigatório.',
            'status.required' => 'O campo Status é obrigatório.',
        ];

        $this->validate($request, $rules, $messages);
    }

    private function statusOptions(): array
    {
        return [
            'ativo' => 'Ativo',
            'inativo' => 'Inativo',
        ];
    }

    private function normalizeNullableText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $normalized = Str::of($text)->trim()->toString();

        return $normalized === '' ? null : $normalized;
    }

    private function getEmpresaId(): int
    {
        $empresaId = request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;

        abort_unless($empresaId, 403, 'Empresa não encontrada para o usuário autenticado.');

        return (int) $empresaId;
    }
}
