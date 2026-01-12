<?php

namespace App\Models\Petshop;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacinacaoSessaoDose extends Model
{
    use HasFactory;

    protected $table = 'petshop_vacinacao_sessao_doses';

    protected $fillable = [
        'sessao_id',
        'dose_planejada_id',
        'aplicada_em',
        'responsavel_id',
        'lote_id',
        'quantidade_ml',
        'via_aplicacao',
        'local_anatomico',
        'temperatura_pet',
        'observacoes',
        'resultado',
        'motivo_nao_aplicacao',
    ];

    protected $casts = [
        'sessao_id' => 'integer',
        'dose_planejada_id' => 'integer',
        'aplicada_em' => 'datetime',
        'responsavel_id' => 'integer',
        'lote_id' => 'integer',
        'quantidade_ml' => 'decimal:2',
        'temperatura_pet' => 'decimal:1',
    ];

    public const RESULT_APLICADA = 'aplicada';
    public const RESULT_REAGENDADA = 'reagendada';
    public const RESULT_NAO_APLICADA = 'nao_aplicada';

    public static function resultLabels()
    {
        return [
            self::RESULT_APLICADA => 'Aplicada',
            self::RESULT_REAGENDADA => 'Reagendada',
            self::RESULT_NAO_APLICADA => 'Não aplicada',
        ];
    }

    public function sessao()
    {
        return $this->belongsTo(VacinacaoSessao::class, 'sessao_id');
    }

    public function dosePlanejada()
    {
        return $this->belongsTo(VacinacaoDose::class, 'dose_planejada_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }
}
