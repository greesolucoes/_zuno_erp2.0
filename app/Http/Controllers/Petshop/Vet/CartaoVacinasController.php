<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Vacinacao;
use App\Models\Petshop\VacinacaoDose;
use App\Models\Petshop\VacinacaoSessaoDose;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartaoVacinasController extends Controller
{
    public function index(Request $request): View|ViewFactory
    {
        $cards = $this->fetchVaccinationCards();

        $filters = $this->buildFilters($cards);
        $statistics = $this->buildStatistics($cards);

        return view('petshop.vet.cartao_vacinas.index', [
            'cards' => $cards->all(),
            'filters' => $filters,
            'statistics' => $statistics,
        ]);
    }

    public function create(): View|ViewFactory
    {
        $vaccines = [
            'Polivalente V8/V10',
            'Antirrábica',
            'Gripe Canina (Tosse dos Canis)',
            'Giárdia',
            'Leishmaniose',
            'Tríplice Felina',
            'Quádrupla Felina',
            'Leucemia Felina',
        ];

        $schedules = [
            ['label' => 'Protocolo inicial filhotes', 'value' => 'filhotes'],
            ['label' => 'Reforço anual', 'value' => 'anual'],
            ['label' => 'Plano personalizado', 'value' => 'personalizado'],
            ['label' => 'Campanha sazonal', 'value' => 'sazonal'],
        ];

        $professionals = [
            ['label' => 'Dra. Ana Lima', 'value' => 'ana-lima'],
            ['label' => 'Dr. Pedro Souza', 'value' => 'pedro-souza'],
            ['label' => 'Dra. Mariana Costa', 'value' => 'mariana-costa'],
            ['label' => 'Dr. Renato Oliveira', 'value' => 'renato-oliveira'],
        ];

        $digitalResources = [
            ['icon' => 'ri-smartphone-line', 'title' => 'Envio automático via WhatsApp', 'description' => 'Compartilhe o cartão digital com lembretes e QR Code.'],
            ['icon' => 'ri-notification-3-line', 'title' => 'Lembretes inteligentes', 'description' => 'Programe notificações de pré e pós-vacina para tutores.'],
            ['icon' => 'ri-cloud-line', 'title' => 'Armazenamento seguro', 'description' => 'Histórico completo acessível em qualquer dispositivo.'],
            ['icon' => 'ri-shield-check-line', 'title' => 'Assinatura digital', 'description' => 'Autenticação do veterinário com carimbo eletrônico.'],
        ];

        return view('petshop.vet.cartao_vacinas.create', compact(
            'vaccines',
            'schedules',
            'professionals',
            'digitalResources'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        session()->flash("flash_sucesso", "Cartão digital criado! Esta é uma pré-visualização do fluxo.");

        return redirect()->route('vet.vaccine-cards.index');
    }

    public function print(Request $request, string $card): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            $companyId = $this->resolvePublicCompanyId($request, $card);
        }

        $cardKey = Str::lower($card);

        $cardData = $companyId
            ? $this->fetchVaccinationCards($companyId)
                ->first(fn (array $item) => Str::lower($item['slug']) === $cardKey)
            : $this->findPublicCardBySlug($cardKey);

        abort_if(is_null($cardData), 404);

        return view('petshop.vet.cartao_vacinas.print', [
            'card' => $cardData,
        ]);
    }

    private function fetchVaccinationCards(?int $companyId = null): Collection
    {
        $companyId ??= $this->getEmpresaId();

        if (!$companyId) {
            return collect();
        }

        $vaccinations = Vacinacao::query()
            ->with([
                'animal.cliente.cidade',
                'animal.especie',
                'animal.raca',
                'medico.funcionario',
                'salaAtendimento',
                'doses.vacina',
                'sessions.doses.responsavel',
                'sessions.doses.dosePlanejada',
                'sessions.doses.dosePlanejada.vacina',
                'sessions.responsavel',
            ])
            ->where('empresa_id', $companyId)
            ->orderByDesc('scheduled_at')
            ->get();

        if ($vaccinations->isEmpty()) {
            return collect();
        }

        return $vaccinations
            ->groupBy('animal_id')
            ->map(function (Collection $group) use ($companyId) {
                return $this->mapAnimalVaccinationCard($group, $companyId);
            })
            ->filter()
            ->values();
    }

    private function mapAnimalVaccinationCard(Collection $vaccinations, int $companyId): ?array
    {
        /** @var Vacinacao|null $reference */
        $reference = $vaccinations->first();
        $animal = $reference?->animal;

        if (!$reference || !$animal) {
            return null;
        }

        $slug = $this->buildCardSlug($animal);
        $dosesTotal = (int) $vaccinations->sum(fn (Vacinacao $v) => $v->doses->count());
        $vaccinationEntries = $this->mapVaccinationEntries($vaccinations);

        if ($dosesTotal === 0 && !empty($vaccinationEntries)) {
            $dosesTotal = count($vaccinationEntries);
        }

        $dosesCompleted = (int) $vaccinations->sum(fn (Vacinacao $v) => $this->countAppliedDoses($v));
        $statusValue = $this->resolveCardStatus($vaccinations);
        $statusLabel = Vacinacao::statusOptions()[$statusValue] ?? $this->humanizeStatus($statusValue);
        $statusVariant = $this->mapStatusVariant($statusValue);
        $nextDue = $this->resolveNextDue($vaccinations);
        $lastUpdate = $this->resolveLastUpdate($vaccinations);
        $timeline = $this->buildTimelineFromEntries($vaccinationEntries, $nextDue, $lastUpdate);
        $tags = $this->buildCardTags($animal, $statusLabel, $nextDue, $dosesCompleted, $vaccinationEntries);

        $dosesTotal = max($dosesTotal, $dosesCompleted);

        return [
            'slug' => $slug,
            'patient' => [
                'name' => $this->formatPatientName($animal->nome),
                'species' => $this->titleCase($animal->especie?->nome),
                'breed' => $this->formatBreed($animal),
                'birthdate' => $this->formatBirthdate($animal->data_nascimento),
                'color' => $this->titleCase($animal->cor),
                'gender' => $this->formatGender($animal->sexo),
                'identification' => $this->formatIdentification($animal),
                'avatar' => $this->buildAvatarUrl($animal),
            ],
            'tutor' => [
                'name' => $this->formatTutorName($animal),
                'contact' => $this->formatTutorContact($animal),
                'email' => $this->formatTutorEmail($animal),
            ],
            'status' => [
                'label' => $statusLabel,
                'variant' => $statusVariant,
                'value' => $statusValue,
                'next_due' => $nextDue ? $nextDue->format('d/m/Y') : 'Sem previsão',
                'next_due_iso' => $nextDue?->toDateString(),
                'last_update' => $lastUpdate ? $lastUpdate->format('d/m/Y') : '—',
            ],
            'doses_completed' => $dosesCompleted,
            'doses_total' => $dosesTotal,
            'progress_percentage' => $dosesTotal > 0
                ? (int) round(($dosesCompleted / $dosesTotal) * 100)
                : ($dosesCompleted > 0 ? 100 : 0),
            'vaccinations' => $vaccinationEntries,
            'timeline' => $timeline,
            'tags' => $tags,
            'qr_code_url' => $this->buildQrCodeUrl($slug, $companyId),
        ];
    }

    private function buildFilters(Collection $cards): array
    {
        $species = $cards
            ->pluck('patient.species')
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $label) => [
                'value' => Str::slug($label),
                'label' => $label,
            ])
            ->all();

        $statuses = $cards
            ->map(fn (array $card) => [
                'value' => $card['status']['value'] ?? Str::slug($card['status']['label']),
                'label' => $card['status']['label'],
            ])
            ->unique('value')
            ->values()
            ->all();

        $vaccines = $cards
            ->flatMap(fn (array $card) => collect($card['vaccinations'])->pluck('title'))
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $label) => [
                'value' => Str::slug($label),
                'label' => $label,
            ])
            ->all();

        $tags = $cards
            ->flatMap(fn (array $card) => $card['tags'])
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $label) => [
                'value' => Str::slug($label),
                'label' => $label,
            ])
            ->all();

        return [
            'species' => $species,
            'status' => $statuses,
            'vaccines' => $vaccines,
            'tags' => $tags,
        ];
    }

    private function buildStatistics(Collection $cards): array
    {
        $totalCards = $cards->count();
        $dosesApplied = $cards->sum('doses_completed');

        $upcoming = $cards->filter(function (array $card) {
            $nextDueIso = $card['status']['next_due_iso'] ?? null;

            if (!$nextDueIso) {
                return false;
            }

            try {
                return Carbon::createFromFormat('Y-m-d', $nextDueIso)->isFuture();
            } catch (\Throwable $exception) {
                return false;
            }
        })->count();

        $pending = $cards->filter(function (array $card) {
            $status = $card['status']['value'] ?? null;

            return in_array($status, [
                Vacinacao::STATUS_ATRASADO,
                Vacinacao::STATUS_PENDENTE,
                Vacinacao::STATUS_PENDENTE_VALIDACAO,
            ], true);
        })->count();

        return [
            [
                'label' => 'Cartões ativos',
                'value' => number_format($totalCards, 0, '', '.'),
                'description' => $totalCards === 1
                    ? '1 paciente com histórico digital'
                    : sprintf('%d pacientes com histórico digital', $totalCards),
                'icon' => 'ri-folder-heart-line',
                'variant' => 'primary',
            ],
            [
                'label' => 'Doses registradas',
                'value' => number_format($dosesApplied, 0, '', '.'),
                'description' => 'Aplicações confirmadas no sistema',
                'icon' => 'ri-injection-line',
                'variant' => 'info',
            ],
            [
                'label' => 'Próximos reforços',
                'value' => number_format($upcoming, 0, '', '.'),
                'description' => 'Agendamentos futuros para acompanhamento',
                'icon' => 'ri-calendar-event-line',
                'variant' => 'warning',
            ],
            [
                'label' => 'Pendências',
                'value' => number_format($pending, 0, '', '.'),
                'description' => 'Cartões que exigem atenção imediata',
                'icon' => 'ri-alert-line',
                'variant' => $pending > 0 ? 'danger' : 'success',
            ],
        ];
    }

    private function mapVaccinationEntries(Collection $vaccinations): array
    {
        return $vaccinations
            ->sortByDesc(fn (Vacinacao $vacinacao) => $vacinacao->scheduled_at ?? $vacinacao->updated_at ?? $vacinacao->created_at)
            ->flatMap(function (Vacinacao $vacinacao) {
                $clinic = $this->formatClinicRoom($vacinacao);
                $veterinarian = $this->resolveVeterinarian($vacinacao);

                return $vacinacao->doses
                    ->sortBy(fn (VacinacaoDose $dose) => $dose->dose_ordem ?? $dose->id)
                    ->map(function (VacinacaoDose $dose) use ($vacinacao, $clinic, $veterinarian) {
                        $appliedDose = $this->findAppliedDose($vacinacao, $dose);
                        $applicationDate = $appliedDose?->aplicada_em ?? $vacinacao->scheduled_at;
                        $professionalName = $appliedDose?->responsavel?->name ?? $veterinarian['name'];
                        $planned = $appliedDose?->dosePlanejada ?? $dose;

                        $signature = $this->buildSignature($professionalName ?? $veterinarian['name'], $veterinarian['register'])
                            ?? 'Registro eletrônico';

                        return [
                            'title' => $planned->vacina?->nome ?? $planned->vacina?->codigo ?? 'Vacina planejada',
                            'dose' => $this->formatDoseLabel($planned),
                            'date' => $applicationDate ? $applicationDate->format('d/m/Y') : null,
                            'date_time' => $applicationDate?->timestamp,
                            'professional' => $professionalName,
                            'lot' => $planned->lote ?: $dose->lote,
                            'valid_until' => $planned->validade ? $planned->validade->format('d/m/Y') : null,
                            'clinic' => $clinic ?: 'Sala de vacinação',
                            'signature' => $signature,
                        ];
                    });
            })
            ->values()
            ->all();

        if (empty($entries)) {
            $entries[] = [
                'title' => 'Vacina não registrada',
                'dose' => null,
                'date' => null,
                'date_time' => null,
                'professional' => null,
                'lot' => null,
                'valid_until' => null,
                'clinic' => '—',
                'signature' => 'Registro eletrônico',
            ];
        }

        return $entries;
    }

    private function buildTimelineFromEntries(array $entries, ?Carbon $nextDue, ?Carbon $lastUpdate): array
    {
        $timeline = collect($entries)
            ->filter(fn (array $entry) => !empty($entry['date']))
            ->sortByDesc(fn (array $entry) => $entry['date_time'] ?? 0)
            ->map(function (array $entry) {
                $descriptionParts = collect([
                    $entry['dose'] ?? null,
                    $entry['professional'] ?? null,
                ])->filter();

                return [
                    'date' => $entry['date'],
                    'title' => $entry['title'],
                    'description' => $descriptionParts->isNotEmpty()
                        ? $descriptionParts->implode(' • ')
                        : 'Registro de vacinação',
                ];
            });

        if ($nextDue) {
            $nextDueLabel = $nextDue->format('d/m/Y');

            $timeline->prepend([
                'date' => $nextDueLabel,
                'title' => 'Próxima dose agendada',
                'description' => 'Acompanhamento automático pelo sistema.',
            ]);
        }

        if ($lastUpdate) {
            $timeline->push([
                'date' => $lastUpdate->format('d/m/Y'),
                'title' => 'Atualização do cartão',
                'description' => 'Histórico revisado na última movimentação.',
            ]);
        }

        if ($timeline->isEmpty()) {
            $timeline->push([
                'date' => now()->format('d/m/Y'),
                'title' => 'Cartão criado',
                'description' => 'Aguardando registros de vacinação.',
            ]);
        }

        return $timeline
            ->unique(function (array $entry) {
                return $entry['date'] . $entry['title'];
            })
            ->take(6)
            ->values()
            ->all();
    }

    private function buildCardTags(Animal $animal, string $statusLabel, ?Carbon $nextDue, int $dosesCompleted, array $entries): array
    {
        $tags = collect();

        if ($statusLabel) {
            $tags->push($statusLabel);
        }

        if ($nextDue) {
            $tags->push('Reforço ' . $nextDue->format('d/m'));
        }

        if ($animal->porte) {
            $tags->push('Porte ' . $this->titleCase($animal->porte));
        }

        $age = null;

        if (is_numeric($animal->idade)) {
            $age = (int) $animal->idade;
        }

        if ($age !== null) {
            $tags->push($age . ' ' . ($age === 1 ? 'ano' : 'anos'));
        }

        if ($dosesCompleted > 0) {
            $tags->push('Histórico ativo');
        }

        $firstVaccination = collect($entries)
            ->first(function (array $entry) {
                return !empty($entry['title']) && !empty($entry['date']);
            });

        if ($firstVaccination && !empty($firstVaccination['title'])) {
            $tags->push($firstVaccination['title']);
        }

        if ($tags->isEmpty()) {
            $tags->push('Carteira digital');
        }

        return $tags
            ->filter()
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->take(4)
            ->values()
            ->all();
    }

    private function buildQrCodeUrl(string $slug, ?int $companyId = null): string
    {
        $companyId ??= $this->getEmpresaId();

        $parameters = ['card' => $slug];

        if ($companyId) {
            $parameters['empresa'] = $companyId;
            $parameters['token'] = $this->generateCardToken($companyId, $slug);
        }

        $cardUrl = url(route('vet.vaccine-cards.print', $parameters, false));

        return sprintf('https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=%s', urlencode($cardUrl));
    }

    private function buildCardSlug(Animal $animal): string
    {
        return Str::slug(sprintf('%s-%s', $animal->nome ?? 'cartao', $animal->id ?? uniqid()));
    }

    private function countAppliedDoses(Vacinacao $vacinacao): int
    {
        return $vacinacao->sessions
            ->flatMap(fn ($session) => $session->doses)
            ->filter(function ($dose) {
                if (!$dose instanceof VacinacaoSessaoDose) {
                    return false;
                }

                if ($dose->aplicada_em) {
                    return true;
                }

                return $dose->resultado === VacinacaoSessaoDose::RESULT_APLICADA;
            })
            ->count();
    }

    private function resolveCardStatus(Collection $vaccinations): string
    {
        if ($vaccinations->contains(fn (Vacinacao $v) => $v->status === Vacinacao::STATUS_ATRASADO)) {
            return Vacinacao::STATUS_ATRASADO;
        }

        if ($vaccinations->contains(fn (Vacinacao $v) => $v->status === Vacinacao::STATUS_PENDENTE)) {
            return Vacinacao::STATUS_PENDENTE;
        }

        if ($vaccinations->contains(fn (Vacinacao $v) => $v->status === Vacinacao::STATUS_PENDENTE_VALIDACAO)) {
            return Vacinacao::STATUS_PENDENTE_VALIDACAO;
        }

        $upcoming = $vaccinations
            ->filter(fn (Vacinacao $v) => $v->scheduled_at && $v->scheduled_at->isFuture())
            ->sortBy(fn (Vacinacao $v) => $v->scheduled_at)
            ->first();

        if ($upcoming) {
            return $upcoming->status;
        }

        $latest = $vaccinations
            ->sortByDesc(fn (Vacinacao $v) => $v->scheduled_at ?? $v->updated_at ?? $v->created_at)
            ->first();

        return $latest?->status ?? Vacinacao::STATUS_CONCLUIDO;
    }

    private function mapStatusVariant(string $status): string
    {
        return match ($status) {
            Vacinacao::STATUS_CONCLUIDO => 'success',
            Vacinacao::STATUS_AGENDADO => 'info',
            Vacinacao::STATUS_EM_EXECUCAO => 'primary',
            Vacinacao::STATUS_PENDENTE, Vacinacao::STATUS_PENDENTE_VALIDACAO => 'warning',
            Vacinacao::STATUS_ATRASADO => 'danger',
            Vacinacao::STATUS_CANCELADO => 'secondary',
            default => 'primary',
        };
    }

    private function resolveNextDue(Collection $vaccinations): ?Carbon
    {
        $dates = $vaccinations
            ->map(fn (Vacinacao $v) => $v->scheduled_at)
            ->filter(fn ($date) => $date instanceof Carbon);

        $upcoming = $dates
            ->filter(fn (Carbon $date) => $date->isFuture())
            ->sort()
            ->first();

        if ($upcoming) {
            return $upcoming;
        }

        return $dates
            ->sortDesc()
            ->first();
    }

    private function resolveLastUpdate(Collection $vaccinations): ?Carbon
    {
        $dates = collect();

        foreach ($vaccinations as $vaccination) {
            if (!$vaccination instanceof Vacinacao) {
                continue;
            }

            if ($vaccination->updated_at) {
                $dates->push($vaccination->updated_at);
            }

            foreach ($vaccination->sessions as $session) {
                if ($session->termino_execucao_at) {
                    $dates->push($session->termino_execucao_at);
                }

                if ($session->inicio_execucao_at) {
                    $dates->push($session->inicio_execucao_at);
                }

                foreach ($session->doses as $dose) {
                    if ($dose->aplicada_em) {
                        $dates->push($dose->aplicada_em);
                    }
                }
            }
        }

        if ($dates->isEmpty()) {
            $scheduled = $vaccinations
                ->filter(fn (Vacinacao $v) => $v->scheduled_at)
                ->sortByDesc(fn (Vacinacao $v) => $v->scheduled_at)
                ->first();

            return $scheduled?->scheduled_at;
        }

        return $dates
            ->filter()
            ->sortDesc()
            ->first();
    }

    private function findAppliedDose(Vacinacao $vacinacao, VacinacaoDose $dose): ?VacinacaoSessaoDose
    {
        return $vacinacao->sessions
            ->flatMap(fn ($session) => $session->doses)
            ->first(function ($sessionDose) use ($dose) {
                return $sessionDose instanceof VacinacaoSessaoDose
                    && (int) $sessionDose->dose_planejada_id === (int) $dose->id;
            });
    }

    private function formatClinicRoom(Vacinacao $vacinacao): ?string
    {
        $room = $vacinacao->salaAtendimento;

        if (!$room) {
            return null;
        }

        return $room->nome ?: $room->identificador;
    }

    private function resolveVeterinarian(Vacinacao $vacinacao): array
    {
        $medico = $vacinacao->medico;

        if (!$medico) {
            return ['name' => null, 'register' => null];
        }

        $name = optional($medico->funcionario)->nome ?? $medico->nome ?? null;
        $register = $medico->crmv ?? null;

        return [
            'name' => $name ? trim($name) : null,
            'register' => $register ? trim($register) : null,
        ];
    }

    private function formatDoseLabel(VacinacaoDose $dose): ?string
    {
        if ($dose->dose) {
            return $dose->dose;
        }

        if ($dose->dose_ordem) {
            return sprintf('Dose %s', $dose->dose_ordem);
        }

        return null;
    }

    private function buildSignature(?string $name, ?string $register): ?string
    {
        if (!$name && !$register) {
            return null;
        }

        if ($name && $register) {
            return sprintf('%s • %s', $name, $register);
        }

        return $name ?? $register;
    }

    private function formatPatientName(?string $name): string
    {
        if (!$name) {
            return 'Paciente sem nome';
        }

        return $this->titleCase($name);
    }

    private function formatBreed(Animal $animal): string
    {
        $breed = $animal->raca?->nome;

        if (!$breed) {
            return 'Sem raça definida';
        }

        return $this->titleCase($breed);
    }

    private function formatBirthdate($birthdate): ?string
    {
        if (!$birthdate) {
            return null;
        }

        try {
            return Carbon::parse($birthdate)->format('d/m/Y');
        } catch (\Throwable $exception) {
            return (string) $birthdate;
        }
    }

    private function formatGender(?string $gender): string
    {
        return match (Str::lower((string) $gender)) {
            'macho', 'm' => 'Macho',
            'femea', 'f' => 'Fêmea',
            default => '—',
        };
    }

    private function formatIdentification(Animal $animal): ?string
    {
        if ($animal->chip) {
            return 'Microchip ' . trim($animal->chip);
        }

        return null;
    }

    private function buildAvatarUrl(Animal $animal): string
    {
        $name = $this->formatPatientName($animal->nome);

        return sprintf('https://ui-avatars.com/api/?name=%s&background=5e60ce&color=fff', urlencode($name));
    }

    private function formatTutorName(Animal $animal): string
    {
        $client = $animal->cliente;

        $raw = $client?->razao_social
            ?: $client?->nome_fantasia
            ?: $client?->contato
            ?: 'Tutor não informado';

        return $this->titleCase($raw);
    }

    private function formatTutorContact(Animal $animal): ?string
    {
        $client = $animal->cliente;

        $contact = $client?->telefone
            ?: $client?->telefone_secundario
            ?: $client?->telefone_terciario;

        return $this->formatPhoneNumber($contact);
    }

    private function formatTutorEmail(Animal $animal): ?string
    {
        $email = $animal->cliente?->email;

        return $email ? mb_strtolower($email, 'UTF-8') : null;
    }

    private function formatPhoneNumber(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6));
        }

        if (strlen($digits) === 9) {
            return sprintf('%s-%s', substr($digits, 0, 5), substr($digits, 5));
        }

        return trim($value);
    }

    private function titleCase(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $normalized = mb_strtolower($value, 'UTF-8');

        return mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
    }

    private function humanizeStatus(?string $status): string
    {
        if (!$status) {
            return 'Sem status';
        }

        return Str::of($status)
            ->replace('_', ' ')
            ->lower()
            ->title();
    }

    private function resolvePublicCompanyId(Request $request, string $card): ?int
    {
        $company = $request->query('empresa');
        $token = (string) $request->query('token', '');

        if (!is_numeric($company) || empty($token)) {
            return null;
        }

        $companyId = (int) $company;
        $expected = $this->generateCardToken($companyId, $card);

        if (!hash_equals($expected, $token)) {
            return null;
        }

        return $companyId;
    }

    private function findPublicCardBySlug(string $card): ?array
    {
        $animalId = $this->extractAnimalIdFromSlug($card);

        if (!$animalId) {
            return null;
        }

        $vaccinations = Vacinacao::query()
            ->with([
                'animal.cliente.cidade',
                'animal.especie',
                'animal.raca',
                'medico.funcionario',
                'salaAtendimento',
                'doses.vacina',
                'sessions.doses.responsavel',
                'sessions.doses.dosePlanejada',
                'sessions.doses.dosePlanejada.vacina',
                'sessions.responsavel',
            ])
            ->where('animal_id', $animalId)
            ->orderByDesc('scheduled_at')
            ->get();

        if ($vaccinations->isEmpty()) {
            return null;
        }

        /** @var Vacinacao|null $reference */
        $reference = $vaccinations->first();
        $companyId = $reference?->empresa_id;

        if (!$reference || !$companyId) {
            return null;
        }

        $cardData = $this->mapAnimalVaccinationCard($vaccinations, (int) $companyId);

        if (!$cardData || Str::lower($cardData['slug'] ?? '') !== $card) {
            return null;
        }

        return $cardData;
    }

    private function extractAnimalIdFromSlug(string $card): ?int
    {
        if (preg_match('/-(\d+)$/', $card, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    private function generateCardToken(int $companyId, string $card): string
    {
        $secret = config('app.key') ?: 'vet-card-token';
        $payload = sprintf('%d|%s', $companyId, Str::lower($card));

        return hash_hmac('sha256', $payload, $secret);
    }

    private function getEmpresaId(): ?int
    {
        return request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;
    }
}
