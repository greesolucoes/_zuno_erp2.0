<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Servico;

class EsteticaServico extends Model
{
    use HasFactory;

    protected $table = 'petshop_estetica_servicos';

    protected $fillable = [
        'estetica_id',
        'servico_id',
        'subtotal',
    ];

    public function estetica()
    {
        return $this->belongsTo(Estetica::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }
}
