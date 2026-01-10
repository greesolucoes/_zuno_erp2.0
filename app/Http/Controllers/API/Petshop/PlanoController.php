<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Plano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                    $q->whereNull('local_id')
                        ->orWhere('local_id', $localId);
                });
            })
            ->get();

        return response()->json($planos);
    }

    public function store(Request $request)
    {
        $plano = Plano::create($request->all());
        return response()->json($plano, 201);
    }

    public function show($id)
    {
        $empresaId = $this->resolveEmpresaId(request());
        $localId = $this->resolveLocalId(request());

        $plano = Plano::query()
            ->where('empresa_id', $empresaId)
            ->when($localId, function ($query) use ($localId) {
                $query->where(function ($q) use ($localId) {
                    $q->whereNull('local_id')
                        ->orWhere('local_id', $localId);
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
        $plano->update($request->all());
        return response()->json($plano);
    }

    public function destroy(Plano $plano)
    {
        $plano->delete();
        return response()->json(null, 204);
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
                    $q->whereNull('local_id')
                        ->orWhere('local_id', $localId);
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
}