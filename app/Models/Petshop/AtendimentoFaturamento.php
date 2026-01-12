<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class AtendimentoFaturamento extends Model
{
    protected $table = 'petshop_vet_atendimento_faturamentos';

    protected $fillable = [
        'empresa_id',
        'atendimento_id',
        'total_servicos',
        'total_produtos',
        'total_geral',
        'observacoes',
    ];

    protected $casts = [
        'total_servicos' => 'decimal:2',
        'total_produtos' => 'decimal:2',
        'total_geral' => 'decimal:2',
    ];

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id');
    }

    public function servicos()
    {
        return $this->hasMany(AtendimentoFaturamentoServico::class, 'faturamento_id');
    }

    public function produtos()
    {
        return $this->hasMany(AtendimentoFaturamentoProduto::class, 'faturamento_id');
    }

    protected function totalServicos()
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }

    protected function totalProdutos()
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }

    protected function totalGeral()
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }
}