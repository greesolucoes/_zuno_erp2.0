<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Checklist;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class ChecklistController extends Controller
{
    public function index(Request $request): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        $checklists = Checklist::query()
            ->where('empresa_id', $empresaId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = Str::of((string) $request->input('search'))->trim()->toString();

                if ($term === '') {
                    return;
                }

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('titulo', 'like', "%{$term}%")
                        ->orWhere('descricao', 'like', "%{$term}%");
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

        return view('petshop.vet.checklists.index', [
            'checklists' => $checklists,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create(): View|ViewFactory
    {
        return view('petshop.vet.checklists.create', [
            'statusOptions' => $this->statusOptions(),
            'typeOptions' => $this->typeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'itens' => ['nullable', 'array'],
            'itens.*' => ['nullable', 'string', 'max:255'],
            'tipo' => ['required', 'string', Rule::in(array_keys($this->typeOptions()))],
            'status' => ['required', 'string'],
        ]);

        $status = Str::of($validated['status'])->trim()->toString();

        if (! array_key_exists($status, $this->statusOptions())) {
            $status = 'ativo';
        }

        $type = Str::of($validated['tipo'])->trim()->lower()->toString();

        if (! array_key_exists($type, $this->typeOptions())) {
            $type = array_key_first($this->typeOptions());
        }

        $items = $this->normalizeItems($validated['itens'] ?? null);

        try {
            Checklist::create([
                'empresa_id' => $empresaId,
                'titulo' => Str::of($validated['titulo'])->trim()->toString(),
                'descricao' => $this->normalizeNullableText($validated['descricao'] ?? null),
                'tipo' => $type,
                'itens' => $items ?: null,
                'status' => $status,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['store' => 'Não foi possível salvar o checklist. Tente novamente.']);
        }

        return redirect()
            ->route('vet.checklist.index')
            ->with('flash_success', 'Checklist cadastrado com sucesso.');
    }

    public function edit(Checklist $checklist): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($checklist->empresa_id === $empresaId, 403);

        return view('petshop.vet.checklists.edit', [
            'checklist' => $checklist,
            'statusOptions' => $this->statusOptions(),
            'typeOptions' => $this->typeOptions(),
        ]);
    }

    public function update(Request $request, Checklist $checklist): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($checklist->empresa_id === $empresaId, 403);

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'itens' => ['nullable', 'array'],
            'itens.*' => ['nullable', 'string', 'max:255'],
            'tipo' => ['required', 'string', Rule::in(array_keys($this->typeOptions()))],
            'status' => ['required', 'string'],
        ]);

        $status = Str::of($validated['status'])->trim()->toString();

        if (! array_key_exists($status, $this->statusOptions())) {
            $status = $checklist->status ?? 'ativo';
        }

        $type = Str::of($validated['tipo'])->trim()->lower()->toString();

        if (! array_key_exists($type, $this->typeOptions())) {
            $type = $checklist->tipo ?? array_key_first($this->typeOptions());
        }

        $items = $this->normalizeItems($validated['itens'] ?? null);

        try {
            $checklist->update([
                'titulo' => Str::of($validated['titulo'])->trim()->toString(),
                'descricao' => $this->normalizeNullableText($validated['descricao'] ?? null),
                'tipo' => $type,
                'itens' => $items ?: null,
                'status' => $status,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['update' => 'Não foi possível atualizar o checklist. Tente novamente.']);
        }

        return redirect()
            ->route('vet.checklist.index')
            ->with('flash_success', 'Checklist atualizado com sucesso.');
    }

    public function destroy(Checklist $checklist): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($checklist->empresa_id === $empresaId, 403);

        try {
            $checklist->delete();

            session()->flash('flash_success', 'Checklist removido com sucesso.');
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('flash_error', 'Não foi possível remover o checklist no momento.');
        }

        return redirect()->route('vet.checklist.index');
    }

    private function statusOptions(): array
    {
        return [
            'ativo' => 'Ativo',
            'inativo' => 'Inativo',
        ];
    }

    private function typeOptions(): array
    {
        return [
            'atendimento' => 'Atendimento',
            'prescricao' => 'Prescrição',
            'prontuario' => 'Prontuário',
            'vacinacoes' => 'Vacinações',
            'interacoes' => 'Interações',
        ];
    }

    private function normalizeItems(null|array|string $items): array
    {
        if ($items === null) {
            return [];
        }

        if (is_string($items)) {
            return collect(preg_split('/\r\n|\r|\n/', $items))
                ->map(fn ($item) => Str::of($item)->trim()->toString())
                ->filter()
                ->values()
                ->all();
        }

        return collect($items)
            ->map(function ($item) {
                if (! is_string($item)) {
                    return null;
                }

                return Str::of($item)->trim()->toString();
            })
            ->filter()
            ->values()
            ->all();
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