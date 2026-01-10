<?php

namespace App\Models\Petshop;

use App\Models\Cliente;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Internacao;
use App\Models\Petshop\Pelagem;
use App\Models\Petshop\Raca;
use App\Models\Petshop\Vacinacao;
use App\Traits\UppercaseFillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use HasFactory;
    use UppercaseFillable;
    use SoftDeletes;

    protected $table = 'animais';

    protected $fillable = [
      'cliente_id',  'especie_id', 'raca_id', 'pelagem_id', 'cor', 'nome',
      'data_nascimento', 'peso', 'sexo', 'observacao', 'idade',
      'chip', 'tem_pedigree', 'pedigree', 'porte', 'origem', 'empresa_id'
    ];

    protected $appends = ['animal_info'];

    public function getAnimalInfoAttribute()
    {
        return $this->nome . " - Tutor: " . (isset($this->cliente) ? $this->cliente->razao_social : '--');
    }

    protected function getUppercaseFields()
    {
        return [
            'nome', 'peso', 'sexo', 'observacao','pedigree', 'porte', 'origem', 'cor'
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

    public function cliente(){
      return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function especie(){
      return $this->belongsTo(Especie::class, 'especie_id');
    }

    public function raca(){
      return $this->belongsTo(Raca::class, 'raca_id');
    }

    public function pelagem(){
        return $this->belongsTo(Pelagem::class, 'pelagem_id');
    }

    public function hospitalizations()
    {
        return $this->hasMany(Internacao::class, 'animal_id');
    }

    public function activeHospitalization()
    {
        return $this->hasOne(Internacao::class, 'animal_id')
            ->where('status', Internacao::STATUS_ACTIVE)
            ->latestOfMany('internado_em');
    }

    public function vacinacoes()
    {
        return $this->hasMany(Vacinacao::class, 'animal_id');
    }
}
