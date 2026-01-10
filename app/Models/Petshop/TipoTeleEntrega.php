<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoTeleEntrega extends Model
{
    use HasFactory;

    protected $table = 'tipos_tele_entregas';

    protected $fillable = [
        'empresa_id',  'nome'
    ];

    public function teleEntregas(){
        return $this->hasMany(TeleEntrega::class, 'tipo_id');
    }
}
