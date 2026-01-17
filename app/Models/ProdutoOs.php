<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoOs extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id',
        'ordem_servico_id',
        'quantidade',
        'valor_unitario',
        'sub_total',
        'status',
        'valor',
        'subtotal',
        'desconto',
    ];

    public function getValorAttribute()
    {
        return $this->attributes['valor_unitario'] ?? null;
    }

    public function setValorAttribute($value): void
    {
        $this->attributes['valor_unitario'] = $value;
    }

    public function getSubtotalAttribute()
    {
        return $this->attributes['sub_total'] ?? null;
    }

    public function setSubtotalAttribute($value): void
    {
        $this->attributes['sub_total'] = $value;
    }

    public function setDescontoAttribute($value): void
    {
        // Compat: alguns fluxos passam "desconto" mas a tabela não possui coluna.
    }

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function ordemServico(){
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }
}
