<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Medico;
use App\Models\Petshop\SalaAtendimento;
use App\Models\Servico;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    private const VET_SERVICE_CATEGORY = 'ATENDIMENTO VETERINARIO';

    public function index(Request $request): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        if (! $empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $filters = $this->extractFilters($request);

        $baseQuery = Atendimento::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'tutor',
                'veterinario.funcionario',
                'sala',
                'servico',
            ])
            ->forCompany($empresaId);

        $filteredQuery = $this->applyFilters(clone $baseQuery, $filters);

        $appointmentsCollection = (clone $filteredQuery)
            ->whereNotNull('data_atendimento')
            ->orderBy('data_atendimento')
            ->orderBy('horario')
            ->limit(400)
            ->get();

        $appointments = $appointmentsCollection
            ->map(fn (Atendimento $atendimento) => $this->mapAppointment($atendimento))
            ->values();

        $calendarEvents = $appointments->all();

        $upcomingAppointments = $appointments
            ->filter(fn (array $appointment) => $this->isUpcoming($appointment['start'] ?? null))
            ->sortBy(fn (array $appointment) => $appointment['start_timestamp'] ?? PHP_INT_MAX)
            ->take(8)
            ->values();

        if ($upcomingAppointments->isEmpty()) {
            $upcomingAppointments = $appointments
                ->sortBy(fn (array $appointment) => $appointment['start_timestamp'] ?? PHP_INT_MAX)
                ->take(8)
                ->values();
        }

        $statusLegend = $appointments
            ->filter(fn (array $appointment) => filled($appointment['status']) && filled($appointment['color']))
            ->unique('status_value')
            ->map(fn (array $appointment) => [
                'label' => $appointment['status'],
                'color' => $appointment['color'],
            ])
            ->values()
            ->all();

        $statusSummary = $this->buildStatusSummary(clone $filteredQuery);

        $filtersOptions = [
            'veterinarians' => $this->loadVeterinarians($empresaId),
            'services' => $this->loadServices($empresaId),
            'locations' => $this->loadLocations($empresaId),
        ];

        return view('petshop.vet.agenda.index', [
            'statusSummary' => $statusSummary,
            'filters' => $filtersOptions,
            'calendarEvents' => $calendarEvents,
            'upcomingAppointments' => $upcomingAppointments->all(),
            'statusLegend' => $statusLegend,
        ]);
    }

    private function getEmpresaId(): ?int
    {
        return request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;
    }

    private function extractFilters(Request $request): array
    {
        $start = $this->normalizeDate($request->input('start_date'));
        $end = $this->normalizeDate($request->input('end_date'));

        if ($start && $end && $end < $start) {
            [$start, $end] = [$end, $start];
        }

        return [
            'veterinarian' => $this->sanitizeId($request->input('veterinarian')),
            'service' => $this->sanitizeId($request->input('service')),
            'location' => $this->sanitizeId($request->input('location')),
            'start_date' => $start,
            'end_date' => $end,
        ];
    }

    private function sanitizeId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_INT);

        return $filtered === false ? null : $filtered;
    }

    private function normalizeDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['veterinarian'])) {
            $query->where('veterinario_id', $filters['veterinarian']);
        }

        if (! empty($filters['service'])) {
            $query->where('servico_id', $filters['service']);
        }

        if (! empty($filters['location'])) {
            $query->where('sala_id', $filters['location']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('data_atendimento', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('data_atendimento', '<=', $filters['end_date']);
        }

        return $query;
    }

    private function buildStatusSummary(Builder $query): array
    {
        $template = [
            Atendimento::STATUS_SCHEDULED => ['label' => 'Agendados', 'color' => 'primary'],
            Atendimento::STATUS_IN_PROGRESS => ['label' => 'Em andamento', 'color' => 'warning'],
            Atendimento::STATUS_COMPLETED => ['label' => 'Concluídos', 'color' => 'success'],
            Atendimento::STATUS_CANCELLED => ['label' => 'Cancelados', 'color' => 'danger'],
        ];

        $counts = $query
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect($template)
            ->map(function (array $item, string $status) use ($counts) {
                return [
                    'label' => $item['label'],
                    'color' => $item['color'],
                    'value' => (int) ($counts[$status] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function loadVeterinarians(int $empresaId): array
    {
        return Medico::query()
            ->with('funcionario')
            ->where('empresa_id', $empresaId)
            ->get()
            ->sortBy(function (Medico $medico) {
                return mb_strtolower($medico->funcionario?->nome ?? $medico->crmv ?? '', 'UTF-8');
            })
            ->values()
            ->map(function (Medico $medico) {
                $name = $medico->funcionario?->nome;

                if (! $name && $medico->crmv) {
                    $name = 'CRMV ' . $medico->crmv;
                }

                return [
                    'id' => $medico->id,
                    'name' => $name ?: 'Veterinário #' . $medico->id,
                ];
            })
            ->all();
    }

    private function loadServices(int $empresaId): array
    {
        return Servico::query()
            ->where('empresa_id', $empresaId)
            ->whereHas('categoria', function (Builder $query) {
                $query->where('nome', self::VET_SERVICE_CATEGORY);
            })
            ->orderBy('nome')
            ->limit(200)
            ->get()
            ->map(fn (Servico $servico) => [
                'id' => $servico->id,
                'name' => $servico->nome,
            ])
            ->values()
            ->all();
    }

    private function loadLocations(int $empresaId): array
    {
        return SalaAtendimento::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get()
            ->map(function (SalaAtendimento $sala) {
                $name = $sala->nome ?: $sala->identificador;

                return [
                    'id' => $sala->id,
                    'name' => $name ?: 'Sala #' . $sala->id,
                ];
            })
            ->values()
            ->all();
    }

    private function mapAppointment(Atendimento $atendimento): array
    {
        $animal = $atendimento->animal;
        $tutor = $atendimento->tutor ?: $animal?->cliente;
        $start = $atendimento->start_at?->copy();

        $duration = $atendimento->servico?->tempo_execucao;
        $end = null;

        if ($start) {
            $defaultDuration = 30;
            $durationValue = is_numeric($duration) && (int) $duration > 0
                ? (int) $duration
                : $defaultDuration;

            $end = $start->copy()->addMinutes($durationValue);
        }

        $locale = app()->getLocale() ?: 'pt_BR';

        $veterinarian = $atendimento->veterinario?->funcionario?->nome;

        if (! $veterinarian && $atendimento->veterinario?->crmv) {
            $veterinarian = 'CRMV ' . $atendimento->veterinario->crmv;
        }

        $room = $atendimento->sala?->nome ?: $atendimento->sala?->identificador;

        $notes = $atendimento->observacoes_triagem ?: $atendimento->motivo_visita;

        if ($notes !== null) {
            $notes = trim(strip_tags((string) $notes));
            $notes = $notes === '' ? null : $notes;
        }

        $startHuman = $start?->copy()->locale($locale)->translatedFormat('d \d\e M [às] H\hi');
        $startDay = $start?->copy()->locale($locale)->translatedFormat('ddd, d \d\e MMM');
        $timeRange = $start ? $start->format('H:i') : null;

        if ($timeRange && $end) {
            $timeRange .= ' - ' . $end->format('H:i');
        }

        $endIso = $end?->toIso8601String();

        return [
            'id' => $atendimento->id,
            'code' => $atendimento->codigo,
            'date' => $atendimento->data_atendimento?->format('Y-m-d'),
            'time' => $start ? $start->format('H:i') : null,
            'time_range' => $timeRange,
            'start' => $start ? $start->toIso8601String() : null,
            'end' => $endIso,
            'start_timestamp' => $start?->getTimestamp(),
            'status' => $atendimento->status_label,
            'status_value' => $atendimento->status,
            'status_color' => $atendimento->status_color,
            'color' => $this->resolveStatusColor($atendimento->status_color),
            'patient' => $animal?->nome,
            'species' => $animal?->especie?->nome,
            'breed' => $animal?->raca?->nome,
            'tutor' => $atendimento->tutor_nome ?: $this->resolveTutorName($tutor),
            'tutor_contact' => $this->resolveTutorContact($tutor, $atendimento->contato_tutor),
            'tutor_email' => $atendimento->email_tutor ?: ($tutor?->email ?? null),
            'service' => $atendimento->servico?->nome ?: ($atendimento->tipo_atendimento ?: null),
            'veterinarian' => $veterinarian,
            'room' => $room,
            'notes' => $notes,
            'start_human' => $startHuman,
            'day_label' => $startDay,
            'is_today' => $start?->isToday(),
        ];
    }

    private function resolveTutorName(?Cliente $tutor): ?string
    {
        if (! $tutor) {
            return null;
        }

        if (! empty($tutor->nome)) {
            return $tutor->nome;
        }

        if (! empty($tutor->razao_social)) {
            return $tutor->razao_social;
        }

        return null;
    }

    private function resolveStatusColor(?string $statusColor): string
    {
        $palette = [
            'primary' => '#556ee6',
            'success' => '#34c38f',
            'warning' => '#f1b44c',
            'danger' => '#f46a6a',
            'info' => '#50a5f1',
            'secondary' => '#74788d',
        ];

        if ($statusColor && isset($palette[$statusColor])) {
            return $palette[$statusColor];
        }

        return $palette['primary'];
    }

    private function resolveTutorContact(?Cliente $tutor, ?string $contact): ?string
    {
        $candidates = [
            $contact,
            $tutor?->telefone,
            $tutor?->telefone_secundario,
            $tutor?->telefone_terciario,
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);

            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function isUpcoming(?string $start): bool
    {
        if (! $start) {
            return false;
        }

        try {
            $date = Carbon::parse($start);
        } catch (\Throwable $exception) {
            return false;
        }

        return $date->greaterThanOrEqualTo(now()->startOfDay());
    }
}
