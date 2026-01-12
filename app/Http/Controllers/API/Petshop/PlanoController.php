<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Plano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlanoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        $localId = $this->resolveLocalId($request);

        if (!$empresaId) {
            return response()->json([]);
        }

        $planos = Plano::query()
            ->where('empresa_id', $empresaId)
            ->when($localId, function ($query) use ($localId) {
                $query->where(function ($q) use ($localId) {
                    $q->whereNull('filial_id')
                        ->orWhere('filial_id', $localId);
                });
            })
            ->get();

        return response()->json($planos);
    }

    public function store(Request $request)
    {
        $this->_validate($request);

        try {
            $plano = DB::transaction(function () use ($request) {
                $data = $request->all();
                $data['empresa_id'] = $this->resolveEmpresaId($request);
                $data['filial_id'] = $data['filial_id'] ?? $this->resolveLocalId($request);

                return Plano::create($data);
            });

            return response()->json($plano, 201);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $empresaId = $this->resolveEmpresaId(request());
        $localId = $this->resolveLocalId(request());

        $plano = Plano::query()
            ->where('empresa_id', $empresaId)
            ->when($localId, function ($query) use ($localId) {
                $query->where(function ($q) use ($localId) {
                    $q->whereNull('filial_id')
                        ->orWhere('filial_id', $localId);
                });
            })
            ->findOrFail($id);

        $vigente = $plano->versoes()
            ->whereDate('vigente_desde', '<=', now())
            ->where(function ($q) {
                $q->whereNull('vigente_ate')
                    ->orWhereDate('vigente_ate', '>=', now());
            })
            ->exists();

        $plano->setAttribute('vigente', $vigente);

        return response()->json($plano);
    }

    public function update(Request $request, Plano $plano)
    {
        $empresaId = $this->resolveEmpresaId($request);
        if ($empresaId && (int) $plano->empresa_id !== (int) $empresaId) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $this->_validate($request, true);

        try {
            DB::transaction(function () use ($request, $plano) {
                $plano->update($request->all());
            });
            return response()->json($plano);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(Plano $plano)
    {
        $empresaId = $this->resolveEmpresaId(request());
        if ($empresaId && (int) $plano->empresa_id !== (int) $empresaId) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        try {
            DB::transaction(function () use ($plano) {
                $plano->delete();
            });

            return response()->json(null, 204);
        } catch (\Exception $e) {
            __saveLogError($e, $empresaId ?? request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function pesquisa(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        $localId = $this->resolveLocalId($request);

        if (!$empresaId) {
            return response()->json([]);
        }

        $planos = Plano::where('ativo', 1)
            ->where('empresa_id', $empresaId)
            ->when($localId, function ($query) use ($localId) {
                $query->where(function ($q) use ($localId) {
                    $q->whereNull('filial_id')
                        ->orWhere('filial_id', $localId);
                });
            })
            ->when($request->filled('pesquisa'), function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->pesquisa . '%');
            })
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json($planos);
    }

    private function resolveEmpresaId(Request $request)
    {
        return $request->empresa_id
            ?? $request->empresa
            ?? request()->empresa_id;
    }

    private function resolveLocalId(Request $request)
    {
        if ($request->filled('local_id')) {
            return $request->local_id;
        }

        if (!Auth::check()) {
            return null;
        }

        $local = __getLocalAtivo();

        return is_object($local) ? $local->id : null;
    }

    private function _validate(Request $request, bool $isUpdate = false)
    {
        $rules = [
            'nome' => 'required|max:255',
            'ativo' => 'nullable',
        ];
        $messages = [
            'nome.required' => 'O nome é obrigatório.',
        ];

        $this->validate($request, $rules, $messages);
    }
}
