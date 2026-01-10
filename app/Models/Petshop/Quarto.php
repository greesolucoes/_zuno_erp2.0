<?php

namespace App\Models\Petshop;

use App\Models\Empresa;
use App\Models\Funcionario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quarto extends Model
{
    use HasFactory;

    public const STATUS_DISPONIVEL = 'disponivel';
    public const STATUS_EM_LIMPEZA = 'em_limpeza';
    public const STATUS_MANUTENCAO = 'manutencao';
    public const STATUS_EM_USO = 'em_uso';
    public const STATUS_RESERVADO = 'reservado';
    public const STATUS_BLOQUEADO = 'bloqueado';

    protected $table = 'quartos';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'nome',
        'descricao',
        'tipo',
        'capacidade',
        'status',
    ];

    protected function getUppercaseFields()
    {
        return [
            'nome',
            'descricao',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            foreach ($model->getUppercaseFields() as $field) {
                if (isset($model->$field)) {
                    $model->$field = mb_strtoupper($model->$field, 'UTF-8');
                }
            }
        });
    }

    public static function statusList()
    {
        return [
            self::STATUS_DISPONIVEL => 'Disponível',
            self::STATUS_EM_LIMPEZA => 'Em Limpeza',
            self::STATUS_MANUTENCAO => 'Manutenção/Organização',
            self::STATUS_EM_USO => 'Em uso com animal',
            self::STATUS_RESERVADO => 'Reservado para serviço',
            self::STATUS_BLOQUEADO => 'Bloqueado',
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

    public function eventos()
    {
        return $this->hasMany(QuartoEvento::class);
    }

    public function reservasHotel()
    {
        return $this->hasMany(Hotel::class, 'quarto_id');
    }
}