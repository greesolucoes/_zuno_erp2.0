<?php

namespace App\Models\Petshop;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacinacaoEvento extends Model
{
    use HasFactory;

    protected $table = 'petshop_vacinacao_eventos';

    protected $fillable = [
        'vacinacao_id',
        'tipo',
        'payload',
        'registrado_por',
        'registrado_em',
    ];

    protected $casts = [
        'vacinacao_id' => 'integer',
        'payload' => 'array',
        'registrado_por' => 'integer',
        'registrado_em' => 'datetime',
    ];

    public const TIPO_AGENDAMENTO_CRIADO = 'agendamento_criado';
    public const TIPO_LEMBRETE_ENVIADO = 'lembrete_enviado';
    public const TIPO_SESSAO_INICIADA = 'sessao_iniciada';
    public const TIPO_DOSE_APLICADA = 'dose_aplicada';
    public const TIPO_SESSAO_FINALIZADA = 'sessao_finalizada';
    public const TIPO_CANCELAMENTO = 'cancelamento';
    public const TIPO_REAGENDAMENTO = 'reagendamento';
    public const TIPO_OBSERVACAO = 'observacao_adicionada';

    public static function tipoLabels(): array
    {
        return [
            self::TIPO_AGENDAMENTO_CRIADO => 'Agendamento criado',
            self::TIPO_LEMBRETE_ENVIADO => 'Lembrete enviado',
            self::TIPO_SESSAO_INICIADA => 'Sessão iniciada',
            self::TIPO_DOSE_APLICADA => 'Dose aplicada',
            self::TIPO_SESSAO_FINALIZADA => 'Sessão finalizada',
            self::TIPO_CANCELAMENTO => 'Cancelamento',
            self::TIPO_REAGENDAMENTO => 'Reagendamento',
            self::TIPO_OBSERVACAO => 'Observação adicionada',
        ];
    }

    public function vacinacao(): BelongsTo
    {
        return $this->belongsTo(Vacinacao::class, 'vacinacao_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
