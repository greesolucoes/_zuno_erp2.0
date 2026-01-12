<?php

namespace App\Models\Petshop;

use App\Models\Cliente;
use App\Models\Petshop\Animal;
use App\Models\Petshop\AtendimentoAnexo;
use App\Models\Petshop\Medico;
use App\Models\Petshop\Prescricao;
use App\Models\Petshop\Prontuario;
use App\Models\Petshop\SalaAtendimento;
use App\Models\Petshop\Vacinacao;
use App\Models\Petshop\VetExame;
use App\Models\Servico;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Atendimento extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'agendado';
    public const STATUS_IN_PROGRESS = 'em_andamento';
    public const STATUS_COMPLETED = 'concluido';
    public const STATUS_CANCELLED = 'cancelado';

    protected $table = 'petshop_vet_atendimentos';

    protected $fillable = [
        'empresa_id',
        'animal_id',
        'tutor_id',
        'tutor_nome',
        'contato_tutor',
        'email_tutor',
        'veterinario_id',
        'sala_id',
        'servico_id',
        'data_atendimento',
        'horario',
        'status',
        'tipo_atendimento',
        'motivo_visita',
        'peso',
        'temperatura',
        'frequencia_cardiaca',
        'frequencia_respiratoria',
        'observacoes_triagem',
        'checklists',
        'codigo',
    ];

    protected $casts = [
        'data_atendimento' => 'date',
        'checklists' => 'array',
        'peso' => 'decimal:2',
        'temperatura' => 'decimal:2',
        'frequencia_cardiaca' => 'integer',
        'frequencia_respiratoria' => 'integer',
    ];

    protected $appends = [
        'status_label',
        'status_color',
    ];

    protected function status()
    {
        return Attribute::make(
            get: fn ($value) => match ($value) {
                'rascunho',
                'preparo_finalizado' => self::STATUS_SCHEDULED,
                default => $value,
            },
            set: fn ($value) => match ($value) {
                'rascunho',
                'preparo_finalizado' => self::STATUS_SCHEDULED,
                default => $value,
            },
        );
    }

    protected static function booted()
    {
        static::creating(function (self $atendimento) {
            if (!filled($atendimento->codigo)) {
                $atendimento->codigo = Str::upper(Str::random(12));
            }
        });

        static::created(function (self $atendimento) {
            if (!Str::startsWith((string) $atendimento->codigo, 'ATD-')) {
                $atendimento->forceFill([
                    'codigo' => self::generateCode($atendimento->id),
                ])->save();
            }
        });
    }

    public static function statusMeta()
    {
        return [
            self::STATUS_SCHEDULED => [
                'label' => 'Agendado',
                'color' => 'primary',
            ],
            self::STATUS_IN_PROGRESS => [
                'label' => 'Em atendimento',
                'color' => 'warning',
            ],
            self::STATUS_COMPLETED => [
                'label' => 'Concluído',
                'color' => 'success',
            ],
            self::STATUS_CANCELLED => [
                'label' => 'Cancelado',
                'color' => 'danger',
            ],
        ];
    }

    public static function statusOptions()
    {
        return Arr::mapWithKeys(self::statusMeta(), fn ($meta, $status) => [$status => $meta['label']]);
    }

    public function empresaId()
    {
        return (int) $this->empresa_id;
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function tutor()
    {
        return $this->belongsTo(Cliente::class, 'tutor_id');
    }

    public function veterinario()
    {
        return $this->belongsTo(Medico::class, 'veterinario_id');
    }

    public function sala()
    {
        return $this->belongsTo(SalaAtendimento::class, 'sala_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }

    public function attachments()
    {
        return $this->hasMany(AtendimentoAnexo::class, 'atendimento_id');
    }

    public function vacinacoes()
    {
        return $this->hasMany(Vacinacao::class, 'attendance_id');
    }

    public function latestVaccination()
    {
        return $this->hasOne(Vacinacao::class, 'attendance_id')->latestOfMany('scheduled_at');
    }

    public function faturamento()
    {
        return $this->hasOne(AtendimentoFaturamento::class, 'atendimento_id');
    }

    public function prontuarios()
    {
        return $this->hasMany(Prontuario::class, 'atendimento_id');
    }

    public function latestRecord()
    {
        return $this->hasOne(Prontuario::class, 'atendimento_id')->latestOfMany();
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescricao::class, 'atendimento_id');
    }

    public function latestPrescription()
    {
        return $this->hasOne(Prescricao::class, 'atendimento_id')->latestOfMany();
    }

    public function examRequests()
    {
        return $this->hasMany(VetExame::class, 'atendimento_id');
    }

    public function latestExamRequest()
    {
        return $this->hasOne(VetExame::class, 'atendimento_id')->latestOfMany();
    }

    protected function statusLabel()
    {
        return Attribute::make(
            get: fn () => self::statusMeta()[$this->status]['label'] ?? '—',
        );
    }

    protected function statusColor()
    {
        return Attribute::make(
            get: fn () => self::statusMeta()[$this->status]['color'] ?? 'primary',
        );
    }

    public function getStartAtAttribute()
    {
        if (!$this->data_atendimento) {
            return null;
        }

        $date = $this->data_atendimento instanceof Carbon
            ? $this->data_atendimento->copy()
            : Carbon::parse($this->data_atendimento);

        if ($this->horario) {
            try {
                [$hour, $minute] = explode(':', $this->horario . ':');
                $date->setTime((int) $hour, (int) $minute);
            } catch (\Throwable $exception) {
                return $date;
            }
        }

        return $date;
    }

    public function scopeForCompany($query, int $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function ($builder) use ($term) {
            $builder
                ->where('codigo', 'like', "%{$term}%")
                ->orWhere('tutor_nome', 'like', "%{$term}%")
                ->orWhere('tipo_atendimento', 'like', "%{$term}%")
                ->orWhere('motivo_visita', 'like', "%{$term}%")
                ->orWhereHas('servico', function ($serviceQuery) use ($term) {
                    $serviceQuery->where('nome', 'like', "%{$term}%");
                })
                ->orWhereHas('animal', function ($animalQuery) use ($term) {
                    $animalQuery->where('nome', 'like', "%{$term}%");
                });
        });
    }

    public static function generateCode(int $sequence)
    {
        return 'ATD-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}