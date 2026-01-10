<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function atendimento(): BelongsTo
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id');
    }

    public function servicos(): HasMany
    {
        return $this->hasMany(AtendimentoFaturamentoServico::class, 'faturamento_id');
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(AtendimentoFaturamentoProduto::class, 'faturamento_id');
    }

    protected function totalServicos(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }

    protected function totalProdutos(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }

    protected function totalGeral(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }
}