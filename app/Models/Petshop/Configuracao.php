<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    use HasFactory;

    protected $table = 'petshop_configs';

    protected $fillable = [
        'empresa_id',
        'localizacao_id',
        'usar_agendamento_alternativo',
    ];

    public function horarios()
    {
        return $this->hasMany(HorarioAlternativo::class, 'config_id');
    }
}