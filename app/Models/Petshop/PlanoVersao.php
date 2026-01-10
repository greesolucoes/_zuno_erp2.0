<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanoVersao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'petshop_plano_versoes';

    protected $fillable = [
        'plano_id',
        'vigente_desde',
        'vigente_ate',
    ];

    public function plano()
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    public function servicos()
    {
        return $this->hasMany(PlanoServico::class, 'plano_versao_id');
    }

    public function produtos()
    {
        return $this->hasMany(PlanoProduto::class, 'plano_versao_id');
    }

    public function assinaturas()
    {
        return $this->hasMany(Assinatura::class, 'plano_versao_id');
    }
}