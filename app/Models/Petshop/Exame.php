<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exame extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'animais_exames';

    protected $fillable = [
        'empresa_id',
        'nome',
        'descricao',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
    ];

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('empresa_id', $companyId);
    }

    public function vetExams()
    {
        return $this->hasMany(VetExame::class, 'exame_id');
    }
}