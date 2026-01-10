<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Servico;
use App\Models\Fornecedor;
use App\Models\Funcionario;

class TurmaEvento extends Model
{
    use HasFactory;

    protected $table = 'turma_eventos';

    protected $fillable = [
        'turma_id',
        'servico_id',
        'prestador_id',
        'fornecedor_id',
        'inicio',
        'fim',
        'descricao'
    ];

    protected function getUppercaseFields()
    {
        return [
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

    protected $casts = [
        'inicio' => 'datetime',
        'fim' => 'datetime',
    ];

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function prestador()
    {
        return $this->belongsTo(Funcionario::class, 'prestador_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}