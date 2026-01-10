<?php

namespace App\Models\Petshop;

use App\Models\Petshop\Raca;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Especie extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'animais_especies';

    protected $fillable = [
        'nome', 'empresa_id'
    ];
    
    protected function getUppercaseFields()
    {
        return [
            'nome',
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

    public function racas(){
        return $this->hasMany(Raca::class, 'especie_id');
    }

}
