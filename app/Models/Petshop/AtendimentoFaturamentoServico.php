<?php

namespace App\Models\Petshop;

use App\Models\Servico;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class AtendimentoFaturamentoServico extends Model
{
    protected $table = 'petshop_vet_atendimento_faturamento_servicos';

    protected $fillable = [
        'faturamento_id',
        'empresa_id',
        'servico_id',
        'nome_servico',
        'categoria_servico',
        'data_servico',
        'hora_servico',
        'valor',
    ];

    protected $casts = [
        'data_servico' => 'date',
        'valor' => 'decimal:2',
    ];

    public function faturamento()
    {
        return $this->belongsTo(AtendimentoFaturamento::class, 'faturamento_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }

    protected function valor()
    {
        return Attribute::make(
            set: fn ($value) => $value ?? 0,
        );
    }
}