<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Alergia;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class AlergiasController extends Controller
{
    public function index(Request $request): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        $alergias = Alergia::query()
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

        return view('petshop.vet.alergias.index', [
            'alergias' => $alergias,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create(): View|ViewFactory
    {
        return view('petshop.vet.alergias.create', [
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
            Alergia::create([
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
                ->withErrors(['store' => 'Não foi possível salvar a alergia. Tente novamente.']);
        }

        return redirect()
            ->route('vet.allergies.index')
            ->with('flash_success', 'Alergia cadastrada com sucesso.');
    }

    public function edit(Alergia $alergia): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($alergia->empresa_id === $empresaId, 403);

        return view('petshop.vet.alergias.edit', [
            'alergia' => $alergia,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(Request $request, Alergia $alergia): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($alergia->empresa_id === $empresaId, 403);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'orientacoes' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_keys($this->statusOptions()))],
        ]);

        $status = Str::of($validated['status'])->trim()->toString();

        if (! array_key_exists($status, $this->statusOptions())) {
            $status = $alergia->status ?? 'ativo';
        }

        try {
            $alergia->update([
                'nome' => Str::of($validated['nome'])->trim()->toString(),
                'descricao' => $this->normalizeNullableText($validated['descricao'] ?? null),
                'orientacoes' => $this->normalizeNullableText($validated['orientacoes'] ?? null),
                'status' => $status,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['update' => 'Não foi possível atualizar a alergia. Tente novamente.']);
        }

        return redirect()
            ->route('vet.allergies.index')
            ->with('flash_success', 'Alergia atualizada com sucesso.');
    }

    public function destroy(Alergia $alergia): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($alergia->empresa_id === $empresaId, 403);

        try {
            $alergia->delete();

            session()->flash('flash_success', 'Alergia removida com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível remover a alergia no momento.');
        }

        return redirect()->route('vet.allergies.index');
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