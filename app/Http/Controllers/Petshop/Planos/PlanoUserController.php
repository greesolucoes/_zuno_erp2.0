<?php

namespace App\Http\Controllers\PetShop\Planos;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\ContaReceberParcela;
use App\Models\FormaPagamento;
use App\Models\Petshop\Assinatura;
use App\Models\Petshop\Plano;
use App\Models\PlanoUser;
use App\Models\PortalUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PlanoUserController extends Controller
{
    public function index(Request $request)
    {
        $locais = __getLocaisAtivoUsuario();
        $locais = $locais->pluck(['id']);
        $local_id = $request->get('local_id');

        $usuarios = PlanoUser::with('plano')
            ->where('empresa_id', request()->empresa_id)
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(! $local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            ->when($request->filled('pesquisa'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->pesquisa.'%')
                        ->orWhere('email', 'like', '%'.$request->pesquisa.'%');
                });
            })
            ->orderBy('name')
            ->paginate();

        return view('public.petshop.usuario_form.index', compact('usuarios'));
    }

    public function create()
    {
        return view('public.petshop.usuario_form.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:plano_users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['empresa_id'] = request()->empresa_id;
        $data['local_id'] = optional(__getLocalAtivo())->id;

        PlanoUser::create($data);

        session()->flash('flash_sucesso', 'Usuário do plano criado com sucesso!');
        return redirect()->route('petshop.planos.login');
    }

    public function createInterno()
    {
        $formas_pagamento = FormaPagamento::where('empresa_id', request()->empresa_id)
            ->where('status', 1)
            ->orderBy('nome')
            ->pluck('nome', 'id');

        return view('public.petshop.usuario_form.create', [
            'planos' => true,
            'clientes' => true,
            'formas_pagamento' => $formas_pagamento,
        ]);
    }

    public function storeInterno(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'plano_id' => 'required|exists:petshop_planos,id',
            'data_inicial' => 'required|date',
            'data_final' => 'required|date',
            'valor_integral' => 'required',
            'forma_pagamento_id' => 'required|exists:formas_pagamento,id',
            'data_vencimento' => 'required|date',
            'status' => 'required|boolean',
        ]);

        $cliente = Cliente::findOrFail($data['cliente_id']);
        $plano = Plano::findOrFail($data['plano_id']);

        $vigente = $plano->versoes()
            ->whereDate('vigente_desde', '<=', now())
            ->where(function ($q) {
                $q->whereNull('vigente_ate')
                    ->orWhereDate('vigente_ate', '>=', now());
            })
            ->exists();

        if (! $vigente) {
            return back()->withErrors(['plano_id' => 'O plano selecionado não está vigente.'])->withInput();
        }

        $existingUser = PlanoUser::where('email', $cliente->email)->first();

        if ($existingUser && $existingUser->plano_id) {            return back()->withErrors(['cliente_id' => 'Já existe um usuário do plano com este e-mail.'])->withInput();
        }

         $existingPortalUser = PortalUser::where('email', $cliente->email)->first();

        $password = Str::random(8);
        $hashedPassword = Hash::make($password);

        if ($existingPortalUser) {
            $existingPortalUser->delete();
        }

        $userData = [
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
            'name' => $cliente->razao_social,
            'email' => $cliente->email,
            'password' => $hashedPassword,
            'empresa_id' => request()->empresa_id,
            'local_id' => optional(__getLocalAtivo())->id,
            'data_inicial' => $data['data_inicial'],
            'data_final' => $data['data_final'],
        ];

        if ($existingUser) {
            $existingUser->update($userData);
            $user = $existingUser;
        } else {
            $user = PlanoUser::create($userData);
        }

        Mail::send('mail.plano_user_credentials', [
            'user' => $user,
            'password' => $password,
            'login_url' => route('petshop.planos.login'),
        ], function ($m) use ($user) {
            $m->to($user->email, $user->name)->subject('Acesso ao Plano Petshop');
        });

        $valorTotal = __convert_value_bd($request->valor_integral);
        $valorParcela = $valorTotal;

        $conta = ContaReceber::create([
            'empresa_id' => request()->empresa_id,
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
            'forma_pagamento_id' => $request->forma_pagamento_id,
            'local_id' => optional(__getLocalAtivo())->id,
            'descricao' => 'PLANO PETSHOP '.strtoupper($plano->nome).' CONTRATADO.',
            'valor_integral' => $valorTotal,
            'valor_recebido' => $request->status ? $valorTotal : 0,
            'data_vencimento' => $request->data_vencimento,
            'data_recebimento' => $request->status ? $request->data_vencimento : null,
            'status' => $request->status,
            'created_at' => $data['data_inicial'],
        ]);

        ContaReceberParcela::create([
            'conta_receber_id' => $conta->id,
            'numero' => 1,
            'valor_original' => $valorParcela,
            'valor_atualizado' => $valorParcela,
            'data_vencimento' => $conta->data_vencimento,
            'status' => 'aberta',
        ]);

        session()->flash('flash_sucesso', 'Usuário do plano criado com sucesso!');
        return redirect()->route('petshop.planos.usuario.index');
    }

    public function edit(PlanoUser $planoUser)
    {
        $formas_pagamento = FormaPagamento::where('empresa_id', request()->empresa_id)
            ->where('status', 1)
            ->orderBy('nome')
            ->pluck('nome', 'id');

        $clienteOptions = [$planoUser->cliente_id => $planoUser->cliente->razao_social ?? $planoUser->cliente->nome_fantasia ?? ''];
        $planoOptions = [$planoUser->plano_id => $planoUser->plano->nome];

        $conta = ContaReceber::with(['parcelas' => function ($q) {
            $q->orderBy('numero');
        }])
            ->where('cliente_id', $planoUser->cliente_id)
            ->where('plano_id', $planoUser->plano_id)
            ->first();

        return view('public.petshop.usuario_form.edit', [
            'data' => $planoUser,
            'planos' => $planoOptions,
            'clientes' => $clienteOptions,
            'formas_pagamento' => $formas_pagamento,
            'conta' => $conta,
        ]);
    }

    public function update(Request $request, PlanoUser $planoUser)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'plano_id' => 'required|exists:petshop_planos,id',
        ]);

        $cliente = Cliente::findOrFail($data['cliente_id']);
        $plano = Plano::findOrFail($data['plano_id']);

        $vigente = $plano->versoes()
            ->whereDate('vigente_desde', '<=', now())
            ->where(function ($q) {
                $q->whereNull('vigente_ate')
                    ->orWhereDate('vigente_ate', '>=', now());
            })
            ->exists();

        if (! $vigente) {
            return back()->withErrors(['plano_id' => 'O plano selecionado não está vigente.'])->withInput();
        }

        $planoUser->update([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
            'name' => $cliente->razao_social,
            'email' => $cliente->email,
        ]);

        session()->flash('flash_sucesso', 'Usuário do plano atualizado com sucesso!');
        return redirect()->route('petshop.planos.usuario.index');
    }

    public function cancelarPlano(Request $request)
    {
        Log::info('Iniciando cancelamento de plano', ['plano_user_id' => $request->plano_user_id]);

        try {
            $planoUser = PlanoUser::findOrFail($request->plano_user_id);

            Log::info('PlanoUser encontrado', [
                'id' => $planoUser->id,
                'cliente_id' => $planoUser->cliente_id,
                'plano_id' => $planoUser->plano_id,
            ]);

            $conta = ContaReceber::with('parcelas')
                ->where('cliente_id', $planoUser->cliente_id)
                ->where('plano_id', $planoUser->plano_id)
                ->latest()
                ->first();

            Log::info('Conta encontrada', ['conta_id' => optional($conta)->id]);

            if ($conta) {
                foreach ($conta->parcelas as $parcela) {
                    if ($parcela->status !== 'paga') {
                        $parcela->valor_original = 0;
                        $parcela->valor_atualizado = 0;
                        $parcela->status = 'cancelado';
                        $parcela->save();

                        Log::info('Parcela cancelada', ['parcela_id' => $parcela->id]);
                    }
                }

                $conta->valor_recebido = $conta->pagamentos()->sum('valor_pago');
                $conta->valor_integral = $conta->valor_recebido;
                $conta->status = 'cancelado';
                $conta->save();

                Log::info('Conta cancelada', [
                    'conta_id' => $conta->id,
                    'valor_recebido' => $conta->valor_recebido,
                ]);
            }

            $assinatura = Assinatura::where('cliente_id', $planoUser->cliente_id)
                ->where('plano_id', $planoUser->plano_id)
                ->latest()
                ->first();

            Log::info('Assinatura encontrada', ['assinatura_id' => optional($assinatura)->id]);

            if ($assinatura) {
                $assinatura->status = 'canceled';
                $assinatura->canceled_at = now();
                $assinatura->save();

                Log::info('Assinatura cancelada', ['assinatura_id' => $assinatura->id]);
            }

            $planoUser->plano_id = null;
            $planoUser->save();

            Log::info('PlanoUser desvinculado do plano', ['plano_user_id' => $planoUser->id]);

            Log::info('Plano cancelado com sucesso', ['plano_user_id' => $planoUser->id]);

            return response()->json([
                'success' => true,
                'message' => 'Plano cancelado com sucesso!',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar plano', [
                'plano_user_id' => $request->plano_user_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function renovarPlano(Request $request)
    {
        try {
            $data = $request->validate([
                'plano_user_id' => 'required|exists:plano_users,id',
                'data_inicial' => 'required|date',
                'data_final' => 'required|date|after_or_equal:data_inicial',
                'data_vencimento' => 'required|date|after_or_equal:data_inicial|before_or_equal:data_final',
            ]);

            $planoUser = PlanoUser::with('plano')->findOrFail($data['plano_user_id']);

            if (! $planoUser->plano_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não possui plano para renovar.',
                ], 400);
            }

            $conta = ContaReceber::with('parcelas')
                ->where('cliente_id', $planoUser->cliente_id)
                ->where('plano_id', $planoUser->plano_id)
                ->latest()
                ->first();

            if (! $conta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conta a receber não encontrada.',
                ], 404);
            }

            $valorTotal = $conta->valor_integral;
            $dataInicial = Carbon::parse($data['data_inicial']);
            $dataFinal = Carbon::parse($data['data_final']);
            $dataVencimento = Carbon::parse($data['data_vencimento']);

            if ($dataVencimento->lt($dataInicial) || $dataVencimento->gt($dataFinal)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A data de vencimento deve estar entre a data inicial e final.',
                ], 422);
            }

            $valorParcela = $valorTotal;

            $planoUser->data_inicial = $dataInicial->toDateString();
            $planoUser->data_final = $dataFinal->toDateString();
            $planoUser->save();

            $novaConta = ContaReceber::create([
                'empresa_id' => $conta->empresa_id,
                'cliente_id' => $conta->cliente_id,
                'plano_id' => $conta->plano_id,
                'forma_pagamento_id' => $conta->forma_pagamento_id,
                'local_id' => $conta->local_id,
                'descricao' => $conta->descricao,
                'valor_integral' => $valorTotal,
                'valor_recebido' => 0,
                'data_vencimento' => $dataVencimento->toDateString(),
                'data_recebimento' => null,
                'status' => 0,
                'created_at' => $dataInicial->toDateString(),
            ]);

            ContaReceberParcela::create([
                'conta_receber_id' => $novaConta->id,
                'numero' => 1,
                'valor_original' => $valorParcela,
                'valor_atualizado' => $valorParcela,
                'data_vencimento' => $dataVencimento->toDateString(),
                'status' => 'aberta',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plano renovado com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(PlanoUser $planoUser)
    {
        $planoUser->delete();

        session()->flash('flash_sucesso', 'Usuário do plano removido com sucesso!');
        return redirect()->route('petshop.planos.usuario.index');
    }
    public function reenviarCredenciais(Request $request)
    {
        $data = $request->validate([
            'plano_user_id' => 'required|exists:plano_users,id',
        ]);

        $user = PlanoUser::with('plano')->findOrFail($data['plano_user_id']);

        try {
            $password = Str::random(8);
            $user->update(['password' => Hash::make($password)]);

            Mail::send('mail.plano_user_credentials', [
                'user' => $user,
                'password' => $password,
                'login_url' => route('petshop.planos.login'),
            ], function ($m) use ($user) {
                $m->to($user->email, $user->name)->subject('Acesso ao Plano Petshop');
            });

            return response()->json([
                'success' => true,
                'message' => 'Dados de acesso reenviados com sucesso.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao reenviar credenciais do plano user: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível reenviar o e-mail.',
            ], 500);
        }
    }
}
