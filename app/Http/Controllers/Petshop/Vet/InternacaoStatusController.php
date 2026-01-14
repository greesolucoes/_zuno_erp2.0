<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Internacao;
use App\Models\Petshop\InternacaoStatus;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InternacaoStatusController extends Controller
{
    public function index(Request $request, Internacao $internacao): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($internacao->empresa_id === $empresaId, 403);

        $internacao->loadMissing([
            'animal.cliente',
            'animal.especie',
            'animal.raca',
            'veterinarian.funcionario',
        ]);

        $statuses = $internacao->statusUpdates()
            ->orderByDesc('created_at')
            ->paginate(env("PAGINACAO"))
            ->appends($request->all());

        return view('petshop.vet.internacoes.status.index', [
            'internacao' => $internacao,
            'statuses' => $statuses,
            'evolutionOptions' => InternacaoStatus::evolutionOptions(),
        ]);
    }

    public function create(Internacao $internacao): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($internacao->empresa_id === $empresaId, 403);

        $internacao->loadMissing([
            'animal.cliente',
            'animal.especie',
            'animal.raca',
            'veterinarian.funcionario',
        ]);

        return view('petshop.vet.internacoes.status.create', [
            'internacao' => $internacao,
            'evolutionOptions' => InternacaoStatus::evolutionOptions(),
        ]);
    }

    public function store(Request $request, Internacao $internacao): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($internacao->empresa_id === $empresaId, 403);

        try {
            $this->_validate($request);

            DB::transaction(function () use ($empresaId, $internacao, $request) {
                InternacaoStatus::create([
                    'empresa_id' => $empresaId,
                    'internacao_id' => $internacao->id,
                    'status' => Str::of((string) $request->input('status'))->trim()->limit(120, ''),
                    'anotacao' => $this->normalizeAnnotation($request->input('anotacao')),
                    'evolucao' => $request->input('evolucao'),
                ]);
            });

            session()->flash("flash_sucesso", "Status da internação cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);

            return back()
                ->withInput()
                ->withErrors(['store' => 'Não foi possível salvar o status da internação. Tente novamente.']);
        }

        return redirect()->route('vet.hospitalizations.status.index', $internacao);
    }

    public function edit(Internacao $internacao, InternacaoStatus $status): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($internacao->empresa_id === $empresaId, 403);
        abort_unless($status->empresa_id === $empresaId && $status->internacao_id === $internacao->id, 403);

        $internacao->loadMissing([
            'animal.cliente',
            'animal.especie',
            'animal.raca',
            'veterinarian.funcionario',
        ]);

        return view('petshop.vet.internacoes.status.edit', [
            'internacao' => $internacao,
            'statusRecord' => $status,
            'evolutionOptions' => InternacaoStatus::evolutionOptions(),
        ]);
    }

    public function update(Request $request, Internacao $internacao, InternacaoStatus $status): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($internacao->empresa_id === $empresaId, 403);
        abort_unless($status->empresa_id === $empresaId && $status->internacao_id === $internacao->id, 403);

        try {
            $this->_validate($request);

            DB::transaction(function () use ($status, $request) {
                $status->update([
                    'status' => Str::of((string) $request->input('status'))->trim()->limit(120, ''),
                    'anotacao' => $this->normalizeAnnotation($request->input('anotacao')),
                    'evolucao' => $request->input('evolucao'),
                ]);
            });

            session()->flash("flash_sucesso", "Status da internação atualizado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);

            return back()
                ->withInput()
                ->withErrors(['update' => 'Não foi possível atualizar o status da internação. Tente novamente.']);
        }

        return redirect()->route('vet.hospitalizations.status.index', $internacao);
    }

    public function destroy(Internacao $internacao, InternacaoStatus $status): RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        abort_unless($internacao->empresa_id === $empresaId, 403);
        abort_unless($status->empresa_id === $empresaId && $status->internacao_id === $internacao->id, 403);

        try {
            $status->delete();

            session()->flash("flash_sucesso", "Status da internação removido!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('vet.hospitalizations.status.index', $internacao);
    }

    private function _validate(Request $request): void
    {
        $rules = [
            'status' => ['required', 'string', 'max:120'],
            'anotacao' => ['nullable', 'string'],
            'evolucao' => ['required', 'string', Rule::in(array_keys(InternacaoStatus::evolutionOptions()))],
        ];

        $messages = [
            'status.required' => 'O campo Status é obrigatório.',
            'evolucao.required' => 'O campo Evolução é obrigatório.',
        ];

        $this->validate($request, $rules, $messages);
    }

    private function normalizeAnnotation(?string $annotation): ?string
    {
        if ($annotation === null) {
            return null;
        }

        $text = Str::of($annotation)->trim();

        return $text->isEmpty() ? null : $text->toString();
    }

    private function getEmpresaId(): int
    {
        $empresaId = request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;

        if (! $empresaId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        return (int) $empresaId;
    }
}
