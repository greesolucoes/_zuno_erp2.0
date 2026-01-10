<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaAtendimento extends Model
{
    use HasFactory;

    protected $table = 'petshop_salas_atendimento';

    protected $fillable = [
        'empresa_id',
        'nome',
        'identificador',
        'tipo',
        'capacidade',
        'equipamentos',
        'observacoes',
        'status',
    ];
}