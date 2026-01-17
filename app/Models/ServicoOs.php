<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicoOs extends Model
{
    protected $fillable = [
    	'servico_id',
        'ordem_servico_id',
        'quantidade',
        'status',
        'valor_unitario',
        'sub_total',
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

    public function servico(){
        return $this->belongsTo(Servico::class, 'servico_id');
    }

    public function ordemServico(){
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }
}
