<?php

namespace App\Http\Controllers\Petshop;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Petshop\Configuracao;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Filial;
use Illuminate\Support\Facades\DB;

class ConfiguracaoController extends Controller
{
   public function index(Request $request)
    {
        $empresaId = $request->empresa_id ?? request()->empresa_id;
        $filialId = $request->filial_id ?? request()->filial_id ?? Auth::user()?->filial_id;

        if (!$empresaId) {
            abort(403, 'Empresa não definida para o usuário.');
        }

        $empresa = Empresa::find($empresaId);
        $filial = $filialId ? Filial::find($filialId) : null;

        $empresaSlug = $empresa ? Str::slug($empresa->nome) : 'empresa';
        $filialLabel = $filial?->descricao ?? $filial?->nome_fantasia ?? $filial?->razao_social ?? 'filial';
        $filialSlug = $filial ? Str::slug($filialLabel) : 'filial';

        $link = url("petshop/{$empresaSlug}/{$filialSlug}");

        $config = Configuracao::firstOrCreate(
            [
                'empresa_id' => $empresaId,
                'filial_id' => $filialId,
            ]
        );

        $horarios = $config->horarios()->orderBy('dia_semana')->get();

        return view('petshop.config.index', compact('link', 'config', 'horarios'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);

        $empresaId = $request->empresa_id ?? request()->empresa_id;
        $filialId = $request->filial_id ?? request()->filial_id ?? Auth::user()?->filial_id;

        if (!$empresaId) {
            abort(403, 'Empresa não definida para o usuário.');
        }

        try {
            DB::transaction(function () use ($request, $empresaId, $filialId) {
                $config = Configuracao::firstOrCreate(
                    [
                        'empresa_id' => $empresaId,
                        'filial_id' => $filialId,
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
            });

            session()->flash("flash_sucesso", "Configurações atualizadas!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('petshop.config.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'usar_agendamento_alternativo' => 'nullable|boolean',
            'horarios' => 'nullable|array',
            'horarios.*.dia_semana' => 'nullable',
            'horarios.*.hora_inicio' => 'nullable',
            'horarios.*.hora_fim' => 'nullable',
        ];
        $messages = [];
        $this->validate($request, $rules, $messages);
    }
}
