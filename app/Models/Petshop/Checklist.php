<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_checklists';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'descricao',
        'tipo',
        'itens',
        'status',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'tipo' => 'string',
        'itens' => 'array',
    ];
}