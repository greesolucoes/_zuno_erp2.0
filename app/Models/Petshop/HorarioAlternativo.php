<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioAlternativo extends Model
{
    use HasFactory;

    protected $table = 'petshop_horarios_alternativos';

    protected $fillable = [
        'config_id',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
    ];

    public function configuracao()
    {
        return $this->belongsTo(Configuracao::class, 'config_id');
    }
}