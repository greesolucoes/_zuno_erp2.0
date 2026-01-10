<?php

namespace App\Models\Petshop;

use App\Models\Funcionario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diagnostico extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'petshop_animais_diagnosticos';

    protected $fillable = [
        'empresa_id',
        'animal_id',
        'funcionario_id',
        'nome',
        'descricao',
        'anamnese',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'animal_id' => 'integer',
        'funcionario_id' => 'integer',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
