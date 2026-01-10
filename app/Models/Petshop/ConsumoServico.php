<?php

namespace App\Models\Petshop;

use App\Models\Servico;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumoServico extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'petshop_consumos_servicos';

    protected $fillable = [
        'assinatura_id',
        'servico_id',
        'ciclo_inicio',
        'ciclo_fim',
        'quantidade_usada',
        'used_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'ciclo_inicio' => 'datetime',
        'ciclo_fim' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function assinatura()
    {
        return $this->belongsTo(Assinatura::class, 'assinatura_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}