<?php

namespace App\Models\Petshop;

use App\Models\Cliente;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\InternacaoStatus;
use App\Models\Petshop\Medico;
use App\Models\Petshop\SalaInternacao;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Internacao extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'rascunho';
    public const STATUS_ACTIVE = 'ativo';
    public const STATUS_DISCHARGED = 'alta';
    public const STATUS_CANCELLED = 'cancelado';

    public const RISK_LOW = 'baixo';
    public const RISK_MODERATE = 'moderado';
    public const RISK_HIGH = 'alto';

    protected $table = 'petshop_vet_internacoes';

    protected $fillable = [
        'empresa_id',
        'animal_id',
        'tutor_id',
        'atendimento_id',
        'veterinario_id',
        'sala_internacao_id',
        'status',
        'nivel_risco',
        'internado_em',
        'previsao_alta_em',
        'alta_em',
        'motivo',
        'observacoes',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'animal_id' => 'integer',
        'tutor_id' => 'integer',
        'atendimento_id' => 'integer',
        'veterinario_id' => 'integer',
        'sala_internacao_id' => 'integer',
        'internado_em' => 'datetime',
        'previsao_alta_em' => 'datetime',
        'alta_em' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'risk_label',
        'risk_color',
    ];

    public static function statusMeta(): array
    {
        return [
            self::STATUS_DRAFT => [
                'label' => 'Rascunho',
                'color' => 'secondary',
            ],
            self::STATUS_ACTIVE => [
                'label' => 'Em internação',
                'color' => 'primary',
            ],
            self::STATUS_DISCHARGED => [
                'label' => 'Alta',
                'color' => 'success',
            ],
            self::STATUS_CANCELLED => [
                'label' => 'Cancelado',
                'color' => 'danger',
            ],
        ];
    }

    public static function riskMeta(): array
    {
        return [
            self::RISK_LOW => [
                'label' => 'Baixo',
                'color' => 'success',
            ],
            self::RISK_MODERATE => [
                'label' => 'Moderado',
                'color' => 'warning',
            ],
            self::RISK_HIGH => [
                'label' => 'Alto',
                'color' => 'danger',
            ],
        ];
    }

    public static function statusOptions(): array
    {
        return array_map(
            fn ($meta) => $meta['label'],
            self::statusMeta()
        );
    }

    public static function riskOptions(): array
    {
        return array_map(
            fn ($meta) => $meta['label'],
            self::riskMeta()
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => self::statusMeta()[$this->status]['label'] ?? ucfirst((string) $this->status)
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => self::statusMeta()[$this->status]['color'] ?? 'secondary'
        );
    }

    protected function riskLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nivel_risco
                ? (self::riskMeta()[$this->nivel_risco]['label'] ?? ucfirst($this->nivel_risco))
                : 'Não informado'
        );
    }

    protected function riskColor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nivel_risco
                ? (self::riskMeta()[$this->nivel_risco]['color'] ?? 'secondary')
                : 'secondary'
        );
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'tutor_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id');
    }

    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'veterinario_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(SalaInternacao::class, 'sala_internacao_id');
    }

    public function statusUpdates(): HasMany
    {
        return $this->hasMany(InternacaoStatus::class, 'internacao_id');
    }
}
