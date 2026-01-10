<?php

namespace App\Http\Controllers\PetShop\Planos;

use App\Http\Controllers\Controller;
use App\Models\PortalUser;
use Illuminate\Http\Request;

class UsuarioAvulsoController extends Controller
{
    public function index(Request $request)
    {
        $locais = __getLocaisAtivoUsuario();
        $locais = $locais->pluck(['id']);
        $local_id = $request->get('local_id');

        $usuarios = PortalUser::where('empresa_id', request()->empresa_id)
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            ->when($request->filled('pesquisa'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->pesquisa . '%')
                        ->orWhere('email', 'like', '%' . $request->pesquisa . '%');
                });
            })
            ->orderBy('name')
            ->paginate();

        return view('public.petshop.usuario_avulso.index', compact('usuarios'));
    }

    public function edit(PortalUser $avulsoUser)
    {
        return view('public.petshop.usuario_avulso.edit', [
            'data' => $avulsoUser,
        ]);
    }

    public function update(Request $request, PortalUser $avulsoUser)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:portal_users,email,' . $avulsoUser->id,
        ]);

        $avulsoUser->update($data);

        return redirect()->route('petshop.planos.usuarios-avulso.index')
            ->with('flash_success', 'Usuário avulso atualizado com sucesso!');
    }

    public function destroy(PortalUser $avulsoUser)
    {
        $avulsoUser->delete();

        return redirect()->route('petshop.planos.usuarios-avulso.index')
            ->with('flash_success', 'Usuário avulso removido com sucesso!');
    }
}
