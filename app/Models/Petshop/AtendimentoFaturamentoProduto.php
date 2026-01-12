<?php

namespace App\Models\Petshop;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class AtendimentoFaturamentoProduto extends Model
{
    protected $table = 'petshop_vet_atendimento_faturamento_produtos';

    protected $fillable = [
        'faturamento_id',
        'empresa_id',
        'produto_id',
        'nome_produto',
        'quantidade',
        'valor_unitario',
        'subtotal',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'valor_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function faturamento()
    {
        return $this->belongsTo(AtendimentoFaturamento::class, 'faturamento_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    protected function quantidade()
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }

    protected function valorUnitario()
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }

    protected function subtotal()
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }
}