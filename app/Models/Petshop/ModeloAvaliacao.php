<?php

declare(strict_types=1);

namespace App\Models\Petshop;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeloAvaliacao extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_modelos_avaliacao';

    protected $fillable = [
        'empresa_id',
        'title',
        'category',
        'notes',
        'fields',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'fields' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function atualizador()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}