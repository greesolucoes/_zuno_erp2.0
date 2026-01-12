<?php

namespace App\Models\Petshop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaInternacao extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'disponivel';
    public const STATUS_OCCUPIED = 'ocupada';
    public const STATUS_RESERVED = 'reservada';
    public const STATUS_MAINTENANCE = 'manutencao';

    protected $table = 'petshop_salas_internacao';

    protected $fillable = [
        'empresa_id',
        'nome',
        'identificador',
        'tipo',
        'capacidade',
        'equipamentos',
        'observacoes',
        'status',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'capacidade' => 'integer',
    ];

    public function internacoes()
    {
        return $this->hasMany(Internacao::class, 'sala_internacao_id');
    }
}