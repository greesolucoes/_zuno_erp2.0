<?php

namespace App\Services\Petshop\Vet;

use App\Models\Petshop\Animal;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Prescricao;
use App\Models\Petshop\Prontuario;
use App\Models\Petshop\Vacinacao;
use App\Models\Petshop\VacinacaoEvento;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HistoricoMedicoPacienteService
{
    /**
     * Monta a linha do tempo médica de um paciente.
     */
    public function build(int $empresaId, Animal $animal, int $year): array
    {
        $encounters = Atendimento::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'tutor',
                'veterinario.funcionario',
                'sala',
                'servico',
            ])
            ->where('empresa_id', $empresaId)
            ->where('animal_id', $animal->id)
            ->orderByDesc('data_atendimento')
            ->orderByDesc('horario')
            ->orderByDesc('id')
            ->get();

        $events = collect();
        $availableYears = collect();

        foreach ($encounters as $encounter) {
            $records = Prontuario::query()
                ->with(['veterinario.funcionario'])
                ->forCompany($empresaId)
                ->where('atendimento_id', $encounter->id)
                ->orderByDesc('data_registro')
                ->orderByDesc('created_at')
                ->get();

            $recordIds = $records->pluck('id')->filter()->all();

            $prescriptions = Prescricao::query()
                ->with(['veterinario.funcionario', 'medicamentos'])
                ->where('empresa_id', $empresaId)
                ->where(function ($query) use ($encounter, $recordIds) {
                    $query->where('atendimento_id', $encounter->id);

                    if ($recordIds !== []) {
                        $query->orWhereIn('prontuario_id', $recordIds);
                    }
                })
                ->orderByDesc('emitida_em')
                ->orderByDesc('created_at')
                ->get();

            $vaccinations = Vacinacao::query()
                ->with([
                    'doses.vacina',
                    'sessions.doses',
                    'sessions.responsavel',
                    'eventos.responsavel',
                    'medico.funcionario',
                    'salaAtendimento',
                ])
                ->where('empresa_id', $empresaId)
                ->where('attendance_id', $encounter->id)
                ->orderByDesc('scheduled_at')
                ->orderByDesc('created_at')
                ->get();

            $summary = $this->summarizeEncounter($encounter);
            $encounterEvents = $this->buildEncounterEvents($encounter, $records, $prescriptions, $vaccinations);

            foreach ($encounterEvents as $event) {
                $occurredAt = $event['occurred_at'] ?? null;

                if ($occurredAt && !$occurredAt instanceof Carbon) {
                    try {
                        $occurredAt = Carbon::parse((string) $occurredAt);
                    } catch (\Throwable) {
                        $occurredAt = null;
                    }
                }

                if (!$occurredAt) {
                    $fallback = $encounter->start_at
                        ?: ($encounter->created_at ? Carbon::parse($encounter->created_at) : null);

                    if (!$fallback && $encounter->data_atendimento) {
                        try {
                            $fallback = Carbon::parse($encounter->data_atendimento);
                            if ($encounter->horario) {
                                [$hour, $minute] = explode(':', $encounter->horario . ':');
                                $fallback->setTime((int) $hour, (int) $minute);
                            }
                        } catch (\Throwable) {
                            $fallback = null;
                        }
                    }

                    $occurredAt = $fallback;
                }

                $yearValue = $occurredAt ? (int) $occurredAt->year : $year;
                $monthValue = $occurredAt ? (int) $occurredAt->month : 1;

                if ($occurredAt && (!isset($event['time']) || $event['time'] === '—')) {
                    $event['time'] = $occurredAt->format('d/m/Y H:i');
                }

                $event['occurred_at'] = $occurredAt;
                $event['year'] = $yearValue;
                $event['month'] = $monthValue;
                $event['encounter'] = $summary;

                $events->push($event);
                $availableYears->push($yearValue);
            }
        }

        $availableYearValues = $availableYears
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        if ($availableYearValues->isEmpty()) {
            $availableYearValues = collect([(int) $year]);
        }

        if (!$availableYearValues->contains((int) $year)) {
            $year = (int) $availableYearValues->first();
        }

        $filteredEvents = $events
            ->filter(fn ($event) => (int) $event['year'] === (int) $year)
            ->values();

        $timeline = $filteredEvents
            ->groupBy('month')
            ->sortKeysDesc()
            ->map(function (Collection $items, $month) {
                $label = $this->monthLabel((int) $month);

                $events = $items
                    ->sortBy(function ($event) {
                        return $event['occurred_at'] instanceof Carbon
                            ? $event['occurred_at']->timestamp
                            : PHP_INT_MAX;
                    })
                    ->values()
                    ->map(function ($event) {
                        $details = collect($event['details'] ?? [])
                            ->filter(fn ($value) => filled($value));

                        return array_merge($event, [
                            'details' => $details->all(),
                        ]);
                    })
                    ->all();

                return [
                    'number' => (int) $month,
                    'label' => $label,
                    'events' => $events,
                ];
            })
            ->values()
            ->all();

        $stats = [
            'encounters' => $filteredEvents
                ->filter(fn ($event) => $event['type'] === 'encounter')
                ->pluck('encounter.id')
                ->unique()
                ->count(),
            'records' => $filteredEvents
                ->filter(fn ($event) => $event['type'] === 'record')
                ->pluck('related.id')
                ->unique()
                ->count(),
            'prescriptions' => $filteredEvents
                ->filter(fn ($event) => $event['type'] === 'prescription')
                ->pluck('related.id')
                ->unique()
                ->count(),
            'vaccinations' => $filteredEvents
                ->filter(fn ($event) => $event['type'] === 'vaccination')
                ->pluck('related.id')
                ->unique()
                ->count(),
        ];

        return [
            'selectedYear' => (int) $year,
            'availableYears' => $availableYearValues->all(),
            'timeline' => $timeline,
            'stats' => $stats,
            'hasEvents' => $filteredEvents->isNotEmpty(),
        ];
    }

    protected function buildEncounterEvents(
        Atendimento $atendimento,
        Collection $records,
        Collection $prescriptions,
        Collection $vaccinations
    ): Collection {
        $events = collect();

        $scheduledAt = $atendimento->start_at;
        $createdAt = $atendimento->created_at ? Carbon::parse($atendimento->created_at) : null;
        $initialTimestamp = $createdAt ?? $scheduledAt;

        $events->push([
            'occurred_at' => $initialTimestamp,
            'time' => $initialTimestamp ? $initialTimestamp->format('d/m/Y H:i') : '—',
            'title' => 'Agendamento criado',
            'description' => $scheduledAt
                ? 'Atendimento agendado para ' . $scheduledAt->format('d/m/Y H:i') . '.'
                : 'Atendimento registrado no sistema.',
            'icon' => 'ri-calendar-check-line',
            'details' => collect([
                'Status atual' => $atendimento->status_label,
                'Serviço' => $atendimento->servico?->nome ?: ($atendimento->tipo_atendimento ?: null),
                'Sala' => optional($atendimento->sala)->nome ?: optional($atendimento->sala)->identificador,
                'Veterinário responsável' => optional($atendimento->veterinario?->funcionario)->nome,
                'Código' => $atendimento->codigo,
            ])->filter(fn ($value) => filled($value))->all(),
            'link' => route('vet.atendimentos.history', $atendimento->id),
            'type' => 'encounter',
            'related' => [
                'type' => 'encounter',
                'id' => (int) $atendimento->id,
            ],
        ]);

        $triageDetails = $this->buildTriageDetails($atendimento);

        if ($triageDetails !== []) {
            $triageTimestamp = $atendimento->updated_at ? Carbon::parse($atendimento->updated_at) : $scheduledAt;

            $events->push([
                'occurred_at' => $triageTimestamp,
                'time' => $triageTimestamp ? $triageTimestamp->format('d/m/Y H:i') : '—',
                'title' => 'Triagem registrada',
                'description' => 'Dados de triagem e sinais vitais foram atualizados para o atendimento.',
                'icon' => 'ri-heart-pulse-line',
                'details' => collect($triageDetails)
                    ->mapWithKeys(fn ($item) => [$item['label'] => $item['value']])
                    ->all(),
                'link' => route('vet.atendimentos.history', $atendimento->id),
                'type' => 'triage',
                'related' => [
                    'type' => 'encounter',
                    'id' => (int) $atendimento->id,
                ],
            ]);
        }

        $records->each(function (Prontuario $record) use ($events) {
            $timestamp = $record->data_registro ?: $record->updated_at ?: $record->created_at;
            $occurredAt = $timestamp ? Carbon::parse($timestamp) : null;

            $events->push([
                'occurred_at' => $occurredAt,
                'time' => $occurredAt ? $occurredAt->format('d/m/Y H:i') : '—',
                'title' => 'Consulta médica (' . ($record->codigo ?: 'Prontuário') . ')',
                'description' => $this->buildRecordHeadline($record),
                'icon' => 'ri-stethoscope-line',
                'details' => collect([
                    'Status' => $record->status_label ?? null,
                    'Veterinário' => optional($record->veterinario?->funcionario)->nome,
                ])->filter(fn ($value) => filled($value))->all(),
                'link' => route('vet.records.show', $record->id),
                'type' => 'record',
                'related' => [
                    'type' => 'record',
                    'id' => (int) $record->id,
                ],
            ]);
        });

        $prescriptions->each(function (Prescricao $prescription) use ($events, $atendimento) {
            $timestamp = $prescription->emitida_em ?: $prescription->updated_at ?: $prescription->created_at;
            $occurredAt = $timestamp ? Carbon::parse($timestamp) : null;
            $statusMeta = $this->formatPrescriptionStatus($prescription->status);

            $events->push([
                'occurred_at' => $occurredAt,
                'time' => $occurredAt ? $occurredAt->format('d/m/Y H:i') : '—',
                'title' => 'Prescrição emitida',
                'description' => $this->summarizePrescription($prescription) ?: 'Prescrição vinculada ao atendimento.',
                'icon' => 'ri-file-text-line',
                'details' => collect([
                    'Status' => $statusMeta['label'] ?? null,
                    'Veterinário' => optional($prescription->veterinario?->funcionario)->nome,
                    'Medicamentos' => $prescription->medicamentos
                        ->map(fn ($medication) => $medication->nome ?: $medication->medicamento?->nome)
                        ->filter()
                        ->take(3)
                        ->implode(', '),
                ])->filter(fn ($value) => filled($value))->all(),
                'link' => route('vet.atendimentos.history', $atendimento->id),
                'type' => 'prescription',
                'related' => [
                    'type' => 'prescription',
                    'id' => (int) $prescription->id,
                ],
            ]);
        });

        $this->mapVaccinationEvents($vaccinations)->each(function (array $event) use ($events) {
            $events->push($event);
        });

        return $events;
    }

    protected function summarizeEncounter(Atendimento $atendimento): array
    {
        $start = $atendimento->start_at;
        $veterinarian = $atendimento->veterinario?->funcionario?->nome;

        if (!$veterinarian && $atendimento->veterinario?->crmv) {
            $veterinarian = 'CRMV ' . $atendimento->veterinario->crmv;
        }

        return [
            'id' => (int) $atendimento->id,
            'code' => $atendimento->codigo,
            'status' => $atendimento->status_label,
            'status_color' => $atendimento->status_color,
            'service' => $atendimento->servico?->nome ?: ($atendimento->tipo_atendimento ?: 'Atendimento clínico'),
            'veterinarian' => $veterinarian,
            'room' => optional($atendimento->sala)->nome ?: optional($atendimento->sala)->identificador,
            'start_display' => $start ? $start->format('d/m/Y H:i') : null,
            'history_url' => route('vet.atendimentos.history', $atendimento->id),
        ];
    }

    protected function mapVaccinationEvents(Collection $vaccinations): Collection
    {
        return $vaccinations->flatMap(function (Vacinacao $vaccination) {
            $events = collect();
            $statusMeta = $this->formatVaccinationStatus($vaccination->status);
            $doseLookup = $vaccination->relationLoaded('doses')
                ? $vaccination->doses->keyBy('id')
                : collect();
            $sessionLookup = $vaccination->relationLoaded('sessions')
                ? $vaccination->sessions->keyBy('session_code')
                : collect();

            $vaccination->relationLoaded('eventos')
                ? $vaccination->eventos
                    ->sortBy('registrado_em')
                    ->each(function (VacinacaoEvento $event) use (
                        $events,
                        $vaccination,
                        $statusMeta,
                        $doseLookup,
                        $sessionLookup
                    ) {
                        $payload = $event->payload ?? [];
                        $timestamp = $event->registrado_em ?: $event->created_at;
                        $occurredAt = $timestamp ? Carbon::parse($timestamp) : null;
                        $baseDetails = collect([
                            'Vacinação' => $vaccination->codigo ?: sprintf('Vacinação #%d', $vaccination->id),
                            'Status' => $statusMeta['label'],
                        ]);

                        $title = null;
                        $description = null;
                        $icon = 'ri-syringe-line';
                        $additionalDetails = collect();

                        if (!empty($payload['scheduled_at'])) {
                            $additionalDetails->put('Agendado para', $this->formatDisplayDateTime($payload['scheduled_at']));
                        } elseif ($vaccination->scheduled_at && $event->tipo === VacinacaoEvento::TIPO_AGENDAMENTO_CRIADO) {
                            $additionalDetails->put('Agendado para', $vaccination->scheduled_at->format('d/m/Y H:i'));
                        }

                        switch ($event->tipo) {
                            case VacinacaoEvento::TIPO_AGENDAMENTO_CRIADO:
                                $title = 'Vacinação agendada';
                                $description = 'Agendamento criado para a vacinação deste atendimento.';
                                $icon = 'ri-calendar-event-line';
                                $additionalDetails->put('Sala', optional($vaccination->salaAtendimento)->nome ?: optional($vaccination->salaAtendimento)->identificador);
                                $additionalDetails->put('Veterinário', optional($vaccination->medico?->funcionario)->nome);
                                break;
                            case VacinacaoEvento::TIPO_SESSAO_INICIADA:
                                $title = 'Sessão de vacinação iniciada';
                                $description = 'Execução da vacinação iniciada.';
                                $icon = 'ri-timer-line';
                                $session = $sessionLookup->get($payload['session_code'] ?? null);
                                $additionalDetails->put('Sessão', $payload['session_code'] ?? $session?->session_code);
                                $additionalDetails->put('Responsável', optional($session?->responsavel)->name);
                                break;
                            case VacinacaoEvento::TIPO_DOSE_APLICADA:
                                $dose = $doseLookup->get($payload['dose_planejada_id'] ?? null);
                                $title = 'Dose aplicada';
                                $description = 'Aplicação registrada para a vacinação.';
                                $icon = 'ri-shield-check-line';
                                $additionalDetails->put('Dose', $this->resolveVaccinationDoseName($dose));
                                $additionalDetails->put('Quantidade aplicada', isset($payload['quantidade_ml']) ? (float) $payload['quantidade_ml'] . ' mL' : null);
                                $additionalDetails->put('Via de aplicação', $payload['via_aplicacao'] ?? null);
                                break;
                            case VacinacaoEvento::TIPO_SESSAO_FINALIZADA:
                                $title = 'Sessão de vacinação finalizada';
                                $description = 'Sessão de aplicação encerrada.';
                                $icon = 'ri-check-double-line';
                                $additionalDetails->put('Sessão', $payload['session_code'] ?? null);
                                $additionalDetails->put('Status da sessão', $payload['status'] ?? null);
                                break;
                            case VacinacaoEvento::TIPO_REAGENDAMENTO:
                                $title = 'Dose reagendada';
                                $description = 'Uma dose foi reagendada para outra data.';
                                $icon = 'ri-calendar-todo-line';
                                $dose = $doseLookup->get($payload['dose_planejada_id'] ?? null);
                                $additionalDetails->put('Dose', $this->resolveVaccinationDoseName($dose));
                                $additionalDetails->put('Sessão', $payload['session_code'] ?? null);
                                break;
                            case VacinacaoEvento::TIPO_OBSERVACAO:
                                $title = 'Observação registrada';
                                $description = $payload['message'] ?? 'Uma observação foi adicionada à vacinação.';
                                $icon = 'ri-chat-1-line';
                                $additionalDetails->put('Sessão', $payload['session_code'] ?? null);
                                break;
                            case VacinacaoEvento::TIPO_LEMBRETE_ENVIADO:
                                $title = 'Lembrete enviado';
                                $description = 'Um lembrete foi enviado ao tutor.';
                                $icon = 'ri-notification-line';
                                break;
                            case VacinacaoEvento::TIPO_CANCELAMENTO:
                                $title = 'Vacinação cancelada';
                                $description = 'Vacinação cancelada ou interrompida.';
                                $icon = 'ri-close-circle-line';
                                break;
                            default:
                                $title = VacinacaoEvento::tipoLabels()[$event->tipo] ?? 'Movimentação de vacinação';
                                $description = 'Evento registrado para a vacinação.';
                                break;
                        }

                        $responsible = optional($event->responsavel)->name;
                        if ($responsible) {
                            $additionalDetails->put('Registrado por', $responsible);
                        }

                        $events->push([
                            'occurred_at' => $occurredAt,
                            'time' => $occurredAt ? $occurredAt->format('d/m/Y H:i') : '—',
                            'title' => $title,
                            'description' => $description,
                            'icon' => $icon,
                            'details' => $baseDetails->merge($additionalDetails->filter(fn ($value) => filled($value)))->all(),
                            'link' => route('vet.vaccinations.apply', $vaccination->id),
                            'type' => 'vaccination',
                            'related' => [
                                'type' => 'vaccination',
                                'id' => (int) $vaccination->id,
                            ],
                        ]);
                    })
                : null;

            return $events;
        });
    }

    protected function buildTriageDetails(Atendimento $atendimento): array
    {
        $details = [];

        if (filled($atendimento->motivo_visita)) {
            $details[] = [
                'label' => 'Motivo da visita',
                'value' => $atendimento->motivo_visita,
            ];
        }

        if (filled($atendimento->observacoes_triagem)) {
            $details[] = [
                'label' => 'Observações de triagem',
                'value' => $atendimento->observacoes_triagem,
            ];
        }

        if ($atendimento->peso !== null) {
            $details[] = [
                'label' => 'Peso',
                'value' => number_format((float) $atendimento->peso, 2, ',', '.') . ' kg',
            ];
        }

        if ($atendimento->temperatura !== null) {
            $details[] = [
                'label' => 'Temperatura',
                'value' => number_format((float) $atendimento->temperatura, 2, ',', '.') . ' °C',
            ];
        }

        if ($atendimento->frequencia_cardiaca !== null) {
            $details[] = [
                'label' => 'Frequência cardíaca',
                'value' => (int) $atendimento->frequencia_cardiaca . ' bpm',
            ];
        }

        if ($atendimento->frequencia_respiratoria !== null) {
            $details[] = [
                'label' => 'Frequência respiratória',
                'value' => (int) $atendimento->frequencia_respiratoria . ' mpm',
            ];
        }

        return $details;
    }

    protected function formatPrescriptionStatus(?string $status): array
    {
        $normalized = $status ? Str::lower($status) : null;

        return match ($normalized) {
            'emitida', 'emitido' => ['label' => 'Emitida', 'color' => 'success'],
            'rascunho' => ['label' => 'Rascunho', 'color' => 'warning'],
            'cancelada', 'cancelado' => ['label' => 'Cancelada', 'color' => 'danger'],
            default => [
                'label' => $status ? Str::title($status) : '—',
                'color' => 'secondary',
            ],
        };
    }

    protected function summarizePrescription(Prescricao $prescription): ?string
    {
        $source = $prescription->resumo
            ?: $prescription->orientacoes
            ?: $prescription->diagnostico
            ?: null;

        if (!$source) {
            return null;
        }

        return Str::limit(strip_tags((string) $source), 180);
    }

    protected function buildRecordHeadline(Prontuario $record): string
    {
        $source = $record->resumo_rapido
            ?: $record->resumo
            ?: $record->queixa_principal
            ?: $record->diagnostico_presuntivo
            ?: $record->diagnostico_definitivo
            ?: null;

        if ($source) {
            return Str::limit(strip_tags((string) $source), 200);
        }

        return 'Registro clínico vinculado ao atendimento.';
    }

    protected function formatVaccinationStatus(string $status): array
    {
        $label = Vacinacao::statusOptions()[$status] ?? Str::title((string) $status);

        return [
            'label' => $label,
            'color' => Vacinacao::statusColor($status),
        ];
    }

    protected function resolveVaccinationDoseName($dose): ?string
    {
        if (!$dose) {
            return null;
        }

        $vaccineName = optional($dose->vacina)->nome;
        $order = $dose->dose_ordem ? 'Dose ' . $dose->dose_ordem : null;
        $label = $dose->dose ?: null;

        return collect([$vaccineName, $label ?? $order])
            ->filter()
            ->implode(' - ');
    }

    protected function formatDisplayDateTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('d/m/Y H:i');
        }

        try {
            return Carbon::parse((string) $value)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function monthLabel(int $month): string
    {
        $labels = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];

        return $labels[$month] ?? 'Mês';
    }
}