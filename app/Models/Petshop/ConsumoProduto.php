<?php

namespace App\Models\Petshop;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumoProduto extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'petshop_consumos_produtos';

    protected $fillable = [
        'assinatura_id',
        'produto_id',
        'ciclo_inicio',
        'ciclo_fim',
        'quantidade_usada',
        'unidade',
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

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}