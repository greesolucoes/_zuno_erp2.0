<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alergia extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_alergias';

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