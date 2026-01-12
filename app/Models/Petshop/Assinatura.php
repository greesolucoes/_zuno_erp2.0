<?php

namespace App\Models\Petshop;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assinatura extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'petshop_assinaturas';

    protected $fillable = [
        'cliente_id',
        'plano_id',
        'plano_versao_id',
        'status',
        'started_at',
        'trial_end',
        'cancel_at',
        'canceled_at',
        'billing_interval',
        'interval_count',
        'currency',
        'amount',
        'next_renewal_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'started_at' => 'datetime',
        'trial_end' => 'datetime',
        'cancel_at' => 'datetime',
        'canceled_at' => 'datetime',
        'next_renewal_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    public function versao()
    {
        return $this->belongsTo(PlanoVersao::class, 'plano_versao_id');
    }

    public function consumosServicos()
    {
        return $this->hasMany(ConsumoServico::class, 'assinatura_id');
    }

    public function consumosProdutos()
    {
        return $this->hasMany(ConsumoProduto::class, 'assinatura_id');
    }
}