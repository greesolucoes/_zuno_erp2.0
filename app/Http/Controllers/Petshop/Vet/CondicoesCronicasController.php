<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\CondicaoCronica;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

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
            ->paginate((int) env('PAGINACAO', 15))
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

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'orientacoes' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_keys($this->statusOptions()))],
        ]);

        $status = Str::of($validated['status'])->trim()->toString();

        if (! array_key_exists($status, $this->statusOptions())) {
            $status = 'ativo';
        }

        try {
            CondicaoCronica::create([
                'empresa_id' => $empresaId,
                'nome' => Str::of($validated['nome'])->trim()->toString(),
                'descricao' => $this->normalizeNullableText($validated['descricao'] ?? null),
                'orientacoes' => $this->normalizeNullableText($validated['orientacoes'] ?? null),
                'status' => $status,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['store' => 'Não foi possível salvar a condição crônica. Tente novamente.']);
        }

        return redirect()
            ->route('vet.chronic-conditions.index')
            ->with('flash_success', 'Condição crônica cadastrada com sucesso.');
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

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'orientacoes' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_keys($this->statusOptions()))],
        ]);

        $status = Str::of($validated['status'])->trim()->toString();

        if (! array_key_exists($status, $this->statusOptions())) {
            $status = $condicaoCronica->status ?? 'ativo';
        }

        try {
            $condicaoCronica->update([
                'nome' => Str::of($validated['nome'])->trim()->toString(),
                'descricao' => $this->normalizeNullableText($validated['descricao'] ?? null),
                'orientacoes' => $this->normalizeNullableText($validated['orientacoes'] ?? null),
                'status' => $status,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['update' => 'Não foi possível atualizar a condição crônica. Tente novamente.']);
        }

        return redirect()
            ->route('vet.chronic-conditions.index')
            ->with('flash_success', 'Condição crônica atualizada com sucesso.');
    }

    public function destroy(CondicaoCronica $condicaoCronica): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($condicaoCronica->empresa_id === $empresaId, 403);

        try {
            $condicaoCronica->delete();

            session()->flash('flash_success', 'Condição crônica removida com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível remover a condição crônica no momento.');
        }

        return redirect()->route('vet.chronic-conditions.index');
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
        $empresaId = Auth::user()?->empresa?->empresa_id;

        abort_unless($empresaId, 403, 'Empresa não encontrada para o usuário autenticado.');

        return (int) $empresaId;
    }
}