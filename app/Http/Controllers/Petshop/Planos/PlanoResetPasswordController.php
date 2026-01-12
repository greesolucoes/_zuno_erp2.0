<?php

namespace App\Http\Controllers\PetShop\Planos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanoUser;
use App\Models\PortalUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Mail;
use Carbon\Carbon;

class PlanoResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        $user = PlanoUser::where('email', $request->email)->first();
        if (!$user) {
            $user = PortalUser::where('email', $request->email)->first();
        }
        if ($user == null) {
            session()->flash('flash_erro', 'E-mail não encontrado');
            return back();
        }

        $token = Password::broker()->createToken($user);
        Mail::send('mail.nova_senha_plano', ['token' => $token, 'user' => $user], function ($m) use ($user) {
            $nomeEmail = env('MAIL_FROM_NAME');
            $m->from(env('MAIL_USERNAME'), $nomeEmail);
            $m->subject('Recuperação de Senha');
            $m->to($user->email);
        });

        session()->flash('flash_sucesso', 'Foi enviado um e-mail com um link para redefinir sua senha.');
        return redirect()->route('petshop.planos.login');
    }

    public function resetForm($token)
    {
        return view('petshop.planos.passwords.reset', ['token' => $token]);
    }

    public function validateReset(Request $request)
    {
        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (is_null($reset) || !Hash::check($request->token, $reset->token) || Carbon::parse($reset->created_at)->addMinutes(60)->isPast()) {
            session()->flash('flash_erro', 'O token de redefinição de senha é inválido ou expirou.');
            return redirect()->route('petshop.planos.login');
        }

        $user = PlanoUser::where('email', $request->email)->first();
        if (!$user) {
            $user = PortalUser::where('email', $request->email)->first();
        }
        if (!$user) {
            session()->flash('flash_erro', 'E-mail não encontrado.');
            return redirect()->route('petshop.planos.login');
        }

        $user->update(['password' => Hash::make($request->senha)]);

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        session()->flash('flash_sucesso', 'Sua senha foi redefinida com sucesso!');
        return redirect()->route('petshop.planos.login');
    }
}
