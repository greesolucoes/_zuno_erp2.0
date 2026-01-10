<?php

namespace App\Models\Petshop;

use App\Models\Funcionario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    protected $table = 'petshop_medicos';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'crmv',
        'especialidade',
        'telefone',
        'email',
        'observacoes',
        'status',
    ];

    /**
     * Médicos pertencem a um colaborador cadastrado.
     */
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}