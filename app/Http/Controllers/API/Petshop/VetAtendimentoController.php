<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Medico;
use App\Models\Petshop\SalaAtendimento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VetAtendimentoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'empresa_id' => 'required|integer',
            'paciente_id' => 'required|integer',
            'veterinario_id' => 'required|integer',
            'data_atendimento' => 'required|date_format:Y-m-d',
            'horario' => ['required', 'regex:/^\\d{2}:\\d{2}$/'],
            'sala_id' => 'nullable|integer',
            'tutor_id' => 'nullable|integer',
            'tutor_nome' => 'nullable|string',
            'contato_tutor' => 'nullable|string',
            'email_tutor' => 'nullable|string',
            'tipo_atendimento' => 'nullable|string',
            'motivo_visita' => 'nullable|string',
        ];

        $messages = [
            'paciente_id.required' => 'O paciente é obrigatório.',
            'veterinario_id.required' => 'O veterinário é obrigatório.',
            'data_atendimento.required' => 'A data do atendimento é obrigatória.',
            'horario.required' => 'O horário é obrigatório.',
            'horario.regex' => 'O horário deve estar no formato HH:MM.',
        ];

        $this->validate($request, $rules, $messages);

        $empresaId = (int) $request->empresa_id;

        try {
            $animal = Animal::query()
                ->where('empresa_id', $empresaId)
                ->findOrFail((int) $request->paciente_id);

            $veterinario = Medico::query()
                ->where('empresa_id', $empresaId)
                ->findOrFail((int) $request->veterinario_id);

            if ($request->filled('sala_id')) {
                SalaAtendimento::query()
                    ->where('empresa_id', $empresaId)
                    ->findOrFail((int) $request->sala_id);
            }

            $horario = (string) $request->horario;
            $horario = preg_match('/^\\d{2}:\\d{2}$/', $horario) ? ($horario . ':00') : $horario;

            $payload = [
                'empresa_id' => $empresaId,
                'animal_id' => (int) $animal->id,
                'tutor_id' => $request->tutor_id ?? $animal->cliente_id,
                'tutor_nome' => $request->tutor_nome,
                'contato_tutor' => $request->contato_tutor,
                'email_tutor' => $request->email_tutor,
                'veterinario_id' => (int) $veterinario->id,
                'sala_id' => $request->sala_id,
                'data_atendimento' => Carbon::createFromFormat('Y-m-d', (string) $request->data_atendimento)->format('Y-m-d'),
                'horario' => $horario,
                'status' => Atendimento::STATUS_SCHEDULED,
                'tipo_atendimento' => $request->tipo_atendimento ?: 'Atendimento veterinário',
                'motivo_visita' => $request->motivo_visita,
            ];

            $atendimento = DB::transaction(function () use ($payload) {
                return Atendimento::create($payload);
            });

            return response()->json([
                'success' => true,
                'id' => $atendimento->id,
            ], 200);
        } catch (\Throwable $exception) {
            __saveLogError($exception, $empresaId);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}

