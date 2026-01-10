<?php

namespace App\Http\Controllers\Petshop\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Raca;
use App\Models\Petshop\Pelagem;

class PerfilController extends Controller
{
    /**
     * Show the profile view for plan users.
     */
    public function index()
    {
        $user = Auth::guard('plano')->user() ?? Auth::guard('portal')->user();
        $cliente = optional($user)->cliente;

        $animais = $cliente
            ? $cliente->animais()->with(['especie', 'raca', 'pelagem'])->get()
            : collect();

        $empresaId = $user->empresa_id ?? optional($user->empresa)->id;

        $especies = $empresaId ? Especie::where('empresa_id', $empresaId)->get() : collect();
        $racas = $empresaId ? Raca::where('empresa_id', $empresaId)->get() : collect();
        $pelagens = $empresaId ? Pelagem::where('empresa_id', $empresaId)->get() : collect();

        return view(
            'public.petshop.perfil.index',
            compact('user', 'cliente', 'animais', 'especies', 'racas', 'pelagens')
        );
    }

    public function updateUser(Request $request)
    {
        $user = Auth::guard('plano')->user() ?? Auth::guard('portal')->user();
        $cliente = optional($user)->cliente;

        if ($user) {
            $user->update($request->only('name', 'email'));
        }

        if ($cliente) {
            $cliente->update([
                'telefone' => $request->phone,
                'cpf_cnpj' => $request->cpf,
                'cep' => $request->zip,
                'rua' => $request->street,
                'numero' => $request->number,
                'complemento' => $request->complement,
            ]);
        }

        return back()->with('success', 'Dados do usuário atualizados com sucesso.');
    }

    public function updateAnimal(Request $request, Animal $animal)
    {
        $user = Auth::guard('plano')->user() ?? Auth::guard('portal')->user();
        $cliente = optional($user)->cliente;

        if ($cliente && $animal->cliente_id === $cliente->id) {
            $animal->update([
                'nome' => $request->nome,
                'especie_id' => $request->especie_id,
                'raca_id' => $request->raca_id,
                'pelagem_id' => $request->pelagem_id,
                'data_nascimento' => $request->data_nascimento,
                'peso' => $request->peso,
                'sexo' => $request->sexo,
                'porte' => $request->porte,
                'chip' => $request->chip,
                'tem_pedigree' => $request->tem_pedigree === 'S',
                'pedigree' => $request->pedigree,
                'origem' => $request->origem,
                'observacao' => $request->observacao,
            ]);
        }

        return back()->with('success', 'Dados do pet atualizados com sucesso.');
    }
}