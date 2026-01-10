<?php

namespace App\Models\Petshop;

use App\Models\Filial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plano extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'petshop_planos';

    protected $fillable = [
        'slug',
        'nome',
        'descricao',
        'ativo',
        'empresa_id',
        'local_id',
        'periodo',
        'frequencia_tipo',
        'frequencia_qtd',
        'preco_plano',
        'multa_noshow_tipo',
        'multa_noshow_valor',
        'bloquear_por_inadimplencia',
        'dias_tolerancia_atraso',
    ];

    public function versoes()
    {
        return $this->hasMany(PlanoVersao::class, 'plano_id');
    }

    public function assinaturas()
    {
        return $this->hasMany(Assinatura::class, 'plano_id');
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id');
    }

    public function local()
    {
        return $this->belongsTo(Filial::class, 'local_id');
    }

    public function esteticas()
    {
        return $this->hasMany(Estetica::class, 'plano_id');
    }
}
