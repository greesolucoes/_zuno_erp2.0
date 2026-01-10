<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacinacaoDose extends Model
{
    use HasFactory;

    protected $table = 'petshop_vacinacao_doses';

    protected $fillable = [
        'vacinacao_id',
        'vacina_id',
        'dose_ordem',
        'fabricante',
        'lote',
        'validade',
        'dose',
        'via_administracao',
        'local_anatomico',
        'volume',
        'observacoes',
        'dose_prevista_ml',
        'via_aplicacao_prevista',
        'reforco_intervalo_dias',
        'alertas',
    ];

    protected $casts = [
        'vacinacao_id' => 'integer',
        'vacina_id' => 'integer',
        'dose_ordem' => 'integer',
        'validade' => 'date',
        'dose_prevista_ml' => 'decimal:2',
        'reforco_intervalo_dias' => 'integer',
        'alertas' => 'array',
    ];

    public function vacinacao()
    {
        return $this->belongsTo(Vacinacao::class, 'vacinacao_id');
    }

    public function vacina()
    {
        return $this->belongsTo(Vacina::class, 'vacina_id');
    }
}