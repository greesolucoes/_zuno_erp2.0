<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VetExameAnexo extends Model
{
    use HasFactory;

    public const CONTEXT_REQUEST = 'request';
    public const CONTEXT_COLLECTION = 'collection';

    protected $table = 'petshop_vet_exame_anexos';

    protected $fillable = [
        'exame_id',
        'context',
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

    public function exam()
    {
        return $this->belongsTo(VetExame::class, 'exame_id');
    }
}