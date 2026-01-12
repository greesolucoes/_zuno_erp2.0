<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VetExameAnalise extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_exame_analises';

    protected $fillable = [
        'exame_id',
        'attachment_id',
        'tool_state',
        'viewport_state',
    ];

    protected $casts = [
        'tool_state' => 'array',
        'viewport_state' => 'array',
    ];

    public function exam()
    {
        return $this->belongsTo(VetExame::class, 'exame_id');
    }

    public function attachment()
    {
        return $this->belongsTo(VetExameAnexo::class, 'attachment_id');
    }
}