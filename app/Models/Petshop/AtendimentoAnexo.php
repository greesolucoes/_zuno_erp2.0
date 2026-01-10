<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtendimentoAnexo extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_atendimento_anexos';

    protected $fillable = [
        'atendimento_id',
        'name',
        'path',
        'url',
        'extension',
        'mime_type',
        'size_in_bytes',
        'uploaded_at',
        'uploaded_by',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'size_in_bytes' => 'integer',
    ];

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id');
    }
}