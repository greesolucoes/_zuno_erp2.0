<?php

namespace App\Models\Petshop;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProntuarioEvolucao extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_prontuario_evolucoes';

    protected $fillable = [
        'prontuario_id',
        'categoria',
        'titulo',
        'descricao',
        'registrado_em',
        'registrado_por',
        'dados',
    ];

    protected $casts = [
        'registrado_em' => 'datetime',
        'dados' => 'array',
    ];

    public function prontuario(): BelongsTo
    {
        return $this->belongsTo(Prontuario::class, 'prontuario_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}