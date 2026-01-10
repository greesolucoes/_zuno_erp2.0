<?php

namespace App\Models\Petshop;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\SalaAtendimento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Petshop\VacinacaoEvento;
use App\Models\Petshop\VacinacaoSessao;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

/** @property array|null $observacoes_planejamento */

class Vacinacao extends Model
{
    use HasFactory;
     use SoftDeletes;

    protected $table = 'petshop_vacinacoes';

    protected $fillable = [
        'empresa_id',
        'animal_id',
        'cliente_id',
        'medico_id',
        'attendance_id',
        'sala_atendimento_id',
        'protocolo_id',
        'codigo',
        'status',
        'scheduled_at',
        'scheduled_by',
        'duration_minutes',
        'reminders',
        'checklist',
        'observacoes_planejamento',
        'observacoes_clinicas',
        'observacoes_logistica',
        'instrucoes_tutor',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'animal_id' => 'integer',
        'cliente_id' => 'integer',
        'medico_id' => 'integer',
        'attendance_id' => 'integer',
        'sala_atendimento_id' => 'integer',
        'protocolo_id' => 'integer',
        'scheduled_at' => 'datetime',
        'scheduled_by' => 'integer',
        'duration_minutes' => 'integer',
        'reminders' => 'array',
        'checklist' => 'array',
    ];

    public const STATUS_AGENDADO = 'agendado';
    public const STATUS_EM_EXECUCAO = 'em_execucao';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_PENDENTE_VALIDACAO = 'pendente_validacao';
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_ATRASADO = 'atrasado';
    public const STATUS_CANCELADO = 'cancelado';

    public static function statusOptions()
    {
        return [
            self::STATUS_AGENDADO => 'Agendado',
            self::STATUS_EM_EXECUCAO => 'Em execução',
            self::STATUS_CONCLUIDO => 'Concluído',
            self::STATUS_PENDENTE_VALIDACAO => 'Pendente de validação',
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_ATRASADO => 'Atrasado',
            self::STATUS_CANCELADO => 'Cancelado',
        ];
    }

    public static function statusColor(string $status)
    {
        return [
            self::STATUS_AGENDADO => 'info',
            self::STATUS_EM_EXECUCAO => 'primary',
            self::STATUS_CONCLUIDO => 'success',
            self::STATUS_PENDENTE_VALIDACAO => 'warning',
            self::STATUS_PENDENTE => 'warning',
            self::STATUS_ATRASADO => 'danger',
            self::STATUS_CANCELADO => 'secondary',
        ][$status] ?? 'secondary';
    }

    public static function reminderOptions()
    {
        return [
            'email-48h' => 'Enviar lembrete por e-mail 48h antes',
            'sms-24h' => 'Enviar SMS 24h antes do agendamento',
            'app-12h' => 'Notificação no aplicativo 12h antes',
            'ligacao-4h' => 'Contato telefônico 4h antes',
        ];
    }

    public static function checklistOptions()
    {
        return [
            'check-carteira' => 'Verificar carteira de vacinação e doses anteriores',
            'check-jejum' => 'Confirmar jejum conforme protocolo',
            'check-estado' => 'Avaliar condição clínica e temperatura',
            'check-autorizacao' => 'Garantir assinatura do termo de consentimento',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function protocolo()
    {
        return $this->belongsTo(Vacina::class, 'protocolo_id');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function salaAtendimento()
    {
        return $this->belongsTo(SalaAtendimento::class, 'sala_atendimento_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Atendimento::class, 'attendance_id');
    }

    public function doses()
    {
        return $this->hasMany(VacinacaoDose::class, 'vacinacao_id');
    }

    public function sessions()
    {
        return $this->hasMany(VacinacaoSessao::class, 'vacinacao_id');
    }

    public function eventos()
    {
        return $this->hasMany(VacinacaoEvento::class, 'vacinacao_id');
    }

    public function scheduledByUser()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }
}
