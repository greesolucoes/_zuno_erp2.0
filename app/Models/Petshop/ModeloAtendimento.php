<?php

declare(strict_types=1);

namespace App\Models\Petshop;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModeloAtendimento extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_modelos_atendimento';

    protected $fillable = [
        'empresa_id',
        'title',
        'category',
        'notes',
        'content',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'content' => 'string',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    protected function getUppercaseFields(): array
    {
        return [
            'title',
            'notes',
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

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function atualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}