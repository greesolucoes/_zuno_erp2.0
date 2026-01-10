<?php

namespace App\Models\Petshop;

use App\Models\Empresa;
use App\Models\Funcionario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaDeAula extends Model
{
    use HasFactory;

    protected $table = 'sala_de_aulas';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'nome',
        'descricao',
        'capacidade',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function colaborador()
    {
        return $this->belongsTo(Funcionario::class, 'colaborador_id');
    }
}