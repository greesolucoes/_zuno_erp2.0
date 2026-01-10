<?php

namespace App\Models\Petshop;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacinacaoSessao extends Model
{
    use HasFactory;

    protected $table = 'petshop_vacinacao_sessoes';

    protected $fillable = [
        'vacinacao_id',
        'session_code',
        'inicio_execucao_at',
        'termino_execucao_at',
        'responsavel_id',
        'assistentes_ids',
        'status',
        'observacoes_execucao',
        'assinatura_tutor_path',
    ];

    protected $casts = [
        'vacinacao_id' => 'integer',
        'inicio_execucao_at' => 'datetime',
        'termino_execucao_at' => 'datetime',
        'responsavel_id' => 'integer',
        'assistentes_ids' => 'array',
    ];

    public const STATUS_EM_EXECUCAO = 'em_execucao';
    public const STATUS_CONCLUIDA = 'concluida';
    public const STATUS_ABORTADA = 'abortada';

    public static function statusLabels()
    {
        return [
            self::STATUS_EM_EXECUCAO => 'Em execução',
            self::STATUS_CONCLUIDA => 'Concluída',
            self::STATUS_ABORTADA => 'Abortada',
        ];
    }

    public function vacinacao()
    {
        return $this->belongsTo(Vacinacao::class, 'vacinacao_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function doses()
    {
        return $this->hasMany(VacinacaoSessaoDose::class, 'sessao_id');
    }
}
