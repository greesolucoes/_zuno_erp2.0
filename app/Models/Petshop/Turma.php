<?php

namespace App\Models\Petshop;

use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Petshop\TurmaEvento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Turma extends Model
{
    use HasFactory;

    public const STATUS_DISPONIVEL = 'disponivel';
    public const STATUS_OCUPADO = 'ocupado';
    public const STATUS_EM_LIMPEZA = 'em_limpeza';
    public const STATUS_MANUTENCAO = 'manutencao';

    protected $table = 'turmas';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'nome',
        'descricao',
        'tipo',
        'capacidade',
        'status',
    ];

    public static function statusList()
    {
        return [
            self::STATUS_DISPONIVEL => 'Disponível',
            self::STATUS_OCUPADO => 'Ocupado',
            self::STATUS_EM_LIMPEZA => 'Em Limpeza',
            self::STATUS_MANUTENCAO => 'Manutenção/Organização',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function colaborador()
    {
        return $this->belongsTo(Funcionario::class, 'colaborador_id');
    }

    public function creches()
    {
        return $this->hasMany(Creche::class, 'turma_id');
    }

    public function eventos()
    {
        return $this->hasMany(TurmaEvento::class);
    }
}