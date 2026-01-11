<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrecheChecklist extends Model
{
    use HasFactory;

    protected $table = 'petshop_creche_checklists';

    protected $fillable = [
        'creche_id',
        'empresa_id',
        'tipo',
        'checklist',
    ];

    protected $casts = [
        'checklist' => 'array',
    ];

    public function creche()
    {
        return $this->belongsTo(Creche::class);
    }
}
