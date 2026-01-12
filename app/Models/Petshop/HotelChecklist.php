<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelChecklist extends Model
{
    use HasFactory;

    protected $table = 'petshop_hotel_checklists';

    protected $fillable = [
        'hotel_id',
        'empresa_id',
        'tipo',
        'checklist',
    ];

    protected $casts = [
        'checklist' => 'array',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
