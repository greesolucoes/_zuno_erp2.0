<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoTeleEntrega extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'petshop_tipos_tele_entregas';

    protected $fillable = [
        'empresa_id',  'nome'
    ];

    public function teleEntregas(){
        return $this->hasMany(TeleEntrega::class, 'tipo_id');
    }
}
