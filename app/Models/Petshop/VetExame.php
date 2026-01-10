<?php

namespace App\Models\Petshop;

use App\Models\Petshop\Atendimento;
use App\Models\Petshop\VetExameAnalise;
use App\Models\Petshop\VetExameAnexo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VetExame extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_SOLICITADO = 'solicitado';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_DISPONIVEL_ONLINE = 'disponivel_online';

    public const PENDING_STATUSES = [
        self::STATUS_RASCUNHO,
        self::STATUS_SOLICITADO,
    ];

    public const COMPLETED_STATUSES = [
        self::STATUS_CONCLUIDO,
        self::STATUS_DISPONIVEL_ONLINE,
    ];

    public const PRIORIDADE_NORMAL = 'normal';
    public const PRIORIDADE_URGENTE = 'urgent';
    public const PRIORIDADE_EMERGENCIA = 'emergency';

    protected $table = 'petshop_vet_exames';

    protected $fillable = [
        'empresa_id',
        'atendimento_id',
        'animal_id',
        'medico_id',
        'exame_id',
        'data_prevista_coleta',
        'laboratorio_parceiro',
        'prioridade',
        'observacoes_clinicas',
        'laudo',
        'data_conclusao',
        'status',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'atendimento_id' => 'integer',
        'animal_id' => 'integer',
        'medico_id' => 'integer',
        'exame_id' => 'integer',
        'data_prevista_coleta' => 'date',
        'data_conclusao' => 'datetime',
    ];

    public function scopeForCompany(Builder $query, int $companyId)
    {
        return $query->where('empresa_id', $companyId);
    }

    public static function statusLabels()
    {
        return [
            self::STATUS_RASCUNHO => 'Rascunho',
            self::STATUS_SOLICITADO => 'Solicitado',
            self::STATUS_CONCLUIDO => 'Concluído',
            self::STATUS_DISPONIVEL_ONLINE => 'Disponível online',
        ];
    }

    public static function priorityOptions()
    {
        return [
            self::PRIORIDADE_NORMAL => 'Normal',
            self::PRIORIDADE_URGENTE => 'Urgente',
            self::PRIORIDADE_EMERGENCIA => 'Emergência',
        ];
    }

    public static function pendingStatuses()
    {
        return self::PENDING_STATUSES;
    }

    public static function completedStatuses()
    {
        return self::COMPLETED_STATUSES;
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }

    public function examType()
    {
        return $this->belongsTo(Exame::class, 'exame_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id');
    }

    public function attachments()
    {
        return $this->hasMany(VetExameAnexo::class, 'exame_id');
    }

    public function requestAttachments()
    {
        return $this->attachments()->where('context', VetExameAnexo::CONTEXT_REQUEST);
    }

    public function collectionAttachments()
    {
        return $this->attachments()->where('context', VetExameAnexo::CONTEXT_COLLECTION);
    }

    public function analyses()
    {
        return $this->hasMany(VetExameAnalise::class, 'exame_id');
    }

    public function scopePending(Builder $query)
    {
        return $query->whereIn('status', self::PENDING_STATUSES);
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->whereIn('status', self::COMPLETED_STATUSES);
    }

    public function isPending()
    {
        return in_array($this->status, self::PENDING_STATUSES, true);
    }

    public function isCompleted()
    {
        return in_array($this->status, self::COMPLETED_STATUSES, true);
    }
}