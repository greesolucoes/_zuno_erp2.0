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
        $usuario = Auth::user();
        $empresa = $usuario?->empresa ?? null;
        $filial = $usuario?->filial ?? null;

        $empresaSlug = $empresa ? Str::slug($empresa->nome) : 'empresa';
        $filialLabel = $filial?->descricao ?? $filial?->nome_fantasia ?? $filial?->razao_social ?? 'filial';
        $filialSlug = $filial ? Str::slug($filialLabel) : 'filial';

        $link = url("petshop/{$empresaSlug}/{$filialSlug}");

        $config = Configuracao::firstOrCreate(
            [
                'empresa_id' => $empresa?->id,
                'filial_id' => $filial?->id,
            ]
        );

        $horarios = $config->horarios()->orderBy('dia_semana')->get();

        return view('petshop.config.index', compact('link', 'config', 'horarios'));
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();
        $empresa = $usuario?->empresa ?? null;
        $filial = $usuario?->filial ?? null;

        $config = Configuracao::firstOrCreate(
            [
                'empresa_id' => $empresa?->id,
                'filial_id' => $filial?->id,
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
