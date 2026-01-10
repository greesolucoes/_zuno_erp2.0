<?php

namespace App\Http\Controllers\Petshop;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Petshop\Configuracao;
use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
   public function index()
    {
        $empresa = Auth::user()->empresa->empresa ?? null;
        $local = Auth::user()->locais->first()->localizacao ?? null;

        $empresaSlug = $empresa ? Str::slug($empresa->nome) : 'empresa';
        $localSlug = $local ? Str::slug($local->nome) : 'filial';

        $link = url("petshop/{$empresaSlug}/{$localSlug}");

        $config = Configuracao::firstOrCreate(
            [
                'empresa_id' => $empresa->id ?? null,
                'localizacao_id' => $local->id ?? null,
            ]
        );

        $horarios = $config->horarios()->orderBy('dia_semana')->get();

        return view('petshop.config.index', compact('link', 'config', 'horarios'));
    }

    public function store(Request $request)
    {
        $empresa = Auth::user()->empresa->empresa ?? null;
        $local = Auth::user()->locais->first()->localizacao ?? null;

        $config = Configuracao::firstOrCreate(
            [
                'empresa_id' => $empresa->id ?? null,
                'localizacao_id' => $local->id ?? null,
            ]
        );

        $config->usar_agendamento_alternativo = $request->boolean('usar_agendamento_alternativo');
        $config->save();

        $config->horarios()->delete();

        if ($config->usar_agendamento_alternativo) {
            foreach ($request->input('horarios', []) as $horario) {
                if (
                    isset($horario['dia_semana']) &&
                    $horario['dia_semana'] !== '' &&
                    !empty($horario['hora_inicio']) &&
                    !empty($horario['hora_fim'])
                ) {
                    $config->horarios()->create([
                        'dia_semana' => $horario['dia_semana'],
                        'hora_inicio' => $horario['hora_inicio'],
                        'hora_fim' => $horario['hora_fim'],
                    ]);
                }
            }
        }

        return redirect()->route('petshop.config.index')->with('success', 'Configurações atualizadas!');
    }
}