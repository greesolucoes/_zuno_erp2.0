<?php

namespace App\Models\Petshop;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Medico;
use App\Models\Petshop\ProntuarioEvolucao;

class Prontuario extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'in-progress';
    public const STATUS_AWAITING_REVIEW = 'awaiting-review';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'petshop_vet_prontuarios';

    protected static ?array $columnCache = null;

    protected $fillable = [
        'empresa_id',
        'atendimento_id',
        'animal_id',
        'tutor_id',
        'veterinario_id',
        'modelo_avaliacao_id',
        'codigo',
        'status',
        'tipo',
        'data_registro',
        'resumo_rapido',
        'resumo',
        'queixa_principal',
        'historico_clinico',
        'avaliacao_fisica',
        'diagnostico_presuntivo',
        'diagnostico_definitivo',
        'plano_terapeutico',
        'orientacoes_tutor',
        'observacoes_adicionais',
        'sinais_vitais',
        'avaliacao_personalizada',
        'campos_avaliacao',
        'snapshot_paciente',
        'snapshot_tutor',
        'dados_triagem',
        'lembretes',
        'checklists',
        'comunicacoes',
        'anexos',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_registro' => 'datetime',
        'sinais_vitais' => 'array',
        'avaliacao_personalizada' => 'array',
        'campos_avaliacao' => 'array',
        'snapshot_paciente' => 'array',
        'snapshot_tutor' => 'array',
        'dados_triagem' => 'array',
        'lembretes' => 'array',
        'checklists' => 'array',
        'comunicacoes' => 'array',
        'anexos' => 'array',
        'metadata' => 'array',
    ];

    protected $appends = [
        'status_label',
        'status_color',
    ];

    protected static function booted()
    {
        static::creating(function (self $record) {
            if (!filled($record->codigo)) {
                $record->codigo = Str::upper(Str::random(12));
            }
        });

        static::created(function (self $record) {
            if (!Str::startsWith((string) $record->codigo, 'PRT-')) {
                $record->forceFill([
                    'codigo' => self::generateCode($record->id),
                ])->save();
            }
        });
    }

    public static function statusMeta()
    {
        return [
            self::STATUS_DRAFT => [
                'label' => 'Rascunho',
                'color' => 'secondary',
            ],
            self::STATUS_IN_PROGRESS => [
                'label' => 'Em andamento',
                'color' => 'primary',
            ],
            self::STATUS_AWAITING_REVIEW => [
                'label' => 'Aguardando revisão',
                'color' => 'warning',
            ],
            self::STATUS_FINISHED => [
                'label' => 'Finalizado',
                'color' => 'success',
            ],
            self::STATUS_ARCHIVED => [
                'label' => 'Arquivado',
                'color' => 'info',
            ],
        ];
    }

    public static function statusOptions()
    {
        return Arr::mapWithKeys(self::statusMeta(), static fn ($meta, $status) => [$status => $meta['label']]);
    }

    public static function generateCode(int $sequence)
    {
        $year = now()->format('Y');

        return sprintf('PRT-%s-%05d', $year, $sequence);
    }

    public static function hasColumn(string $column)
    {
        if (self::$columnCache === null) {
            $instance = new self();
            $table = $instance->getTable();

            self::$columnCache = Schema::hasTable($table)
                ? Schema::getColumnListing($table)
                : [];
        }

        return in_array($column, self::$columnCache, true);
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

        $summaryColumn = self::hasColumn('resumo_rapido')
            ? 'resumo_rapido'
            : (self::hasColumn('resumo') ? 'resumo' : null);

        return $query->where(function ($builder) use ($term, $summaryColumn) {
            $builder->where('codigo', 'like', "%{$term}%");

            if ($summaryColumn) {
                $builder->orWhere($summaryColumn, 'like', "%{$term}%");
            }

            $builder
                ->orWhere('queixa_principal', 'like', "%{$term}%")
                ->orWhereHas('animal', function ($animalQuery) use ($term) {
                    $animalQuery->where('nome', 'like', "%{$term}%");
                })
                ->orWhereHas('tutor', function ($tutorQuery) use ($term) {
                    $tutorQuery->where(function ($inner) use ($term) {
                        $inner
                            ->where('razao_social', 'like', "%{$term}%")
                            ->orWhere('nome_fantasia', 'like', "%{$term}%");
                    });
                });
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id');
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

    public function autor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function evolucoes()
    {
        return $this->hasMany(ProntuarioEvolucao::class, 'prontuario_id');
    }

    protected function statusLabel()
    {
        return Attribute::make(
            get: fn () => self::statusMeta()[$this->status]['label'] ?? '—'
        );
    }

    protected function statusColor()
    {
        return Attribute::make(
            get: fn () => self::statusMeta()[$this->status]['color'] ?? 'secondary'
        );
    }
}