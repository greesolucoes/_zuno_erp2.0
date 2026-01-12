<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Diagnostico;
use App\Models\Petshop\Exame;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consulta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'petshop_animais_consultas';

    protected $fillable = [
        'animal_id', 'diagnostico_id', 'exame_id',
        'empresa_id', 'datahora_consulta', 'status', 'observacao',
    ];

    public function animal(){
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function diagnostico(){
        return $this->belongsTo(Diagnostico::class, 'diagnostico_id');
    }

    public function exame(){
        return $this->belongsTo(Exame::class, 'exame_id');
    }

    public function getDataHoraConsulta(){
      return $this->datahora_consulta
      ? date('d/m/Y h:i', strtotime($this->datahora_consulta))
      : '-';
    }

    public function getStatus(){
        if($this->status == 'pendente') return 'Em andamento';
        if($this->status == 'finalizado') return 'Finalizado';
        if($this->status == 'cancelado') return 'Cancelado';
        return 'Outros';
    }

}
