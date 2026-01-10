<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CondicaoCronica extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_condicoes_cronicas';

    protected $fillable = [
        'empresa_id',
        'nome',
        'descricao',
        'orientacoes',
        'status',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
    ];
}