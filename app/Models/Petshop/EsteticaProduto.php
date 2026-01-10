<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produto;

class EsteticaProduto extends Model
{
    use HasFactory;

    protected $table = 'estetica_produtos';

    protected $fillable = [
        'estetica_id',
        'produto_id',
        'quantidade',
        'valor',
        'subtotal',
    ];

    public function estetica()
    {
        return $this->belongsTo(Estetica::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}