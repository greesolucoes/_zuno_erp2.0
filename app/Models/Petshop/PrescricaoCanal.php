<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescricaoCanal extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_prescricao_canais';

    protected $fillable = [
        'prescricao_id',
        'canal',
    ];

    protected $casts = [
        'prescricao_id' => 'integer',
    ];

    public function prescricao()
    {
        return $this->belongsTo(Prescricao::class, 'prescricao_id');
    }
}