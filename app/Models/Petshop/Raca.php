<?php

namespace App\Models\Petshop;

use App\Models\Petshop\Especie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Raca extends Model
{
    use HasFactory;

    protected $table = 'animais_racas';

    protected $fillable = [
        'nome', 'especie_id', 'empresa_id'
    ];

    protected function getUppercaseFields(): array
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

    public function especie(){
        return $this->belongsTo(Especie::class, 'especie_id');
    }

}
