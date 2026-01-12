<?php

namespace App\Models\Petshop;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Petshop\Internacao;

class InternacaoStatus extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_internacao_status';

    protected $fillable = [
        'empresa_id',
        'internacao_id',
        'status',
        'anotacao',
        'evolucao',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'internacao_id' => 'integer',
    ];

    protected $appends = [
        'evolucao_label',
    ];

    public static function evolutionOptions()
    {
        return [
            'sim' => 'Sim',
            'nao' => 'Não',
            'normal' => 'Normal',
        ];
    }

    protected function evolucaoLabel()
    {
        return Attribute::make(
            get: fn () => self::evolutionOptions()[$this->evolucao] ?? ucfirst((string) $this->evolucao)
        );
    }

    public function internacao()
    {
        return $this->belongsTo(Internacao::class, 'internacao_id');
    }
}