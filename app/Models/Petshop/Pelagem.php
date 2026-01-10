<?php

namespace App\Models\Petshop;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Pelagem extends Model
{
    use HasFactory;

    protected $table = 'animais_pelagens';

    protected $fillable = [
        'nome', 'empresa_id',
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

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function animais()
    {
        return $this->hasMany(Animal::class, 'pelagem_id');
    }



}
