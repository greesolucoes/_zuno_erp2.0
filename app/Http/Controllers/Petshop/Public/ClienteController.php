<?php

namespace App\Http\Controllers\Petshop\Public;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Filial;
use App\Models\Cliente;
use App\Models\PortalUser;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Pelagem;
use App\Models\Petshop\Raca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function create(string $empresa, string $filial)
    {
        $empresaModel = Empresa::all()->first(fn ($e) => Str::slug($e->nome) === $empresa);
        abort_if(!$empresaModel, 404);

        $filialModel = Filial::where('empresa_id', $empresaModel->id)
            ->get()
            ->first(function ($item) use ($filial) {
                $nome = $item->descricao ?? $item->nome_fantasia ?? $item->razao_social ?? '';
                return Str::slug($nome) === $filial;
            });
        abort_if(!$filialModel, 404);

        $pelagens = Pelagem::where('empresa_id', $empresaModel->id)->get();
        $especies = Especie::where('empresa_id', $empresaModel->id)->get();
        $racas = Raca::where('empresa_id', $empresaModel->id)->get();

        return view('public.petshop.cliente_form', [
            'empresa' => $empresaModel->nome,
            'filial' => $filialModel->descricao ?? $filialModel->nome_fantasia ?? $filialModel->razao_social,
            'empresaId' => $empresaModel->id,
            'localId' => $filialModel->id,
            'pelagens' => $pelagens,
            'especies' => $especies,
            'racas' => $racas,
        ]);
    }

    public function store(Request $request, string $empresa, string $filial)
    {
        $empresaModel = Empresa::all()->first(fn ($e) => Str::slug($e->nome) === $empresa);
        abort_if(!$empresaModel, 404);

        $filialModel = Filial::where('empresa_id', $empresaModel->id)
            ->get()
            ->first(function ($item) use ($filial) {
                $nome = $item->descricao ?? $item->nome_fantasia ?? $item->razao_social ?? '';
                return Str::slug($nome) === $filial;
            });
        abort_if(!$filialModel, 404);

        $request->merge([
            'status' => 1,
            'contribuinte' => 0,
            'consumidor_final' => 0,
        ]);

        $data = $request->validate([
            'cpf' => 'required',
            'nome' => 'required',
            'nascimento' => 'required|date',
            'whatsapp' => 'required',
            'email' => 'required|email|unique:portal_users,email',
            'cep' => 'required',
            'rua' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
            'cidade_id' => 'required',
            'status' => 'required|boolean',
            'contribuinte' => 'required|boolean',
            'consumidor_final' => 'required|boolean',
        ]);

        $password = Str::random(8);
        $user = null;

        try {
            DB::transaction(function () use ($data, $request, $empresaModel, $filialModel, $password, &$user) {
                $cliente = Cliente::create([
                    'empresa_id' => $empresaModel->id,
                    'razao_social' => $data['nome'],
                    'nome_fantasia' => $data['nome'],
                    'cpf_cnpj' => $data['cpf'],
                    'telefone' => $data['whatsapp'],
                    'telefone_secundario' => $request->input('telefone_fixo'),
                    'email' => $data['email'],
                    'cidade_id' => $request->input('cidade_id'),
                    'cep' => $data['cep'],
                    'rua' => $data['rua'],
                    'numero' => $data['numero'],
                    'bairro' => $data['bairro'],
                    'complemento' => $request->input('complemento'),
                    'status' => $data['status'],
                    'contribuinte' => $data['contribuinte'],
                    'consumidor_final' => $data['consumidor_final'],
                    'data_nascimento' => $data['nascimento'],
                ]);

                $user = PortalUser::create([
                    'name' => $cliente->razao_social,
                    'email' => $cliente->email,
                    'password' => Hash::make($password),
                    'cliente_id' => $cliente->id,
                    'empresa_id' => $empresaModel->id,
                    'local_id' => $filialModel->id,
                ]);

                Animal::create([
                    'cliente_id' => $cliente->id,
                    'pelagem_id' => $request->input('pelagem_id'),
                    'especie_id' => $request->input('especie_id'),
                    'raca_id' => $request->input('raca_id'),
                    'nome' => $request->input('pet_nome'),
                    'sexo' => $request->input('pet_sexo'),
                    'peso' => $request->input('pet_peso'),
                    'porte' => $request->input('pet_porte'),
                    'data_nascimento' => $request->input('pet_nascimento'),
                    'tem_pedigree' => $request->boolean('pet_tem_pedigree'),
                    'pedigree' => $request->input('pet_pedigree', ''),
                    'observacao' => $request->input('pet_observacoes'),
                    'empresa_id' => $empresaModel->id,
                ]);
            });

            Mail::send('mail.portal_user_credentials', [
                'user' => $user,
                'password' => $password,
                'login_url' => route('petshop.planos.login'),
            ], function ($m) use ($user) {
                $m->to($user->email, $user->name)->subject('Acesso ao Portal Petshop');
            });

            Auth::guard('portal')->login($user);
            $request->session()->regenerate();

            return redirect()->route('petshop.planos.agendamentos.novo')->with('flash_success', 'Cadastro realizado com sucesso! Um e-mail com uma senha provisória foi enviado para o seu endereço. Utilize essa senha para acessar o portal e altere-a após o primeiro login.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash_error', 'Ocorreu um erro ao processar seu cadastro. Tente novamente mais tarde.');
        }
    }
}
