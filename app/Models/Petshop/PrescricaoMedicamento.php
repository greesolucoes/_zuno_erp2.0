<?php

namespace App\Models\Petshop;

use App\Models\Petshop\Medicamento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescricaoMedicamento extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_prescricao_medicamentos';

    protected $fillable = [
        'prescricao_id',
        'medicamento_id',
        'nome',
        'dosagem',
        'frequencia',
        'duracao',
        'via',
        'observacoes',
    ];

    protected $casts = [
        'prescricao_id' => 'integer',
        'medicamento_id' => 'integer',
    ];

    public function prescricao(): BelongsTo
    {
        return $this->belongsTo(Prescricao::class, 'prescricao_id');
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'medicamento_id');
    }
}