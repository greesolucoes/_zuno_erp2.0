<?php

namespace App\Models\Petshop;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanoProduto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'petshop_plano_produtos';

    protected $fillable = [
        'plano_versao_id',
        'produto_id',
        'variacao_id',
        'qtd_por_ciclo',
    ];

    /**
     * Expose quantidade attribute used by legacy views.
     */
    protected $appends = ['quantidade'];

    public function getQuantidadeAttribute(): int
    {
        return (int) ($this->qtd_por_ciclo ?? 0);
    }

    public function versao()
    {
        return $this->belongsTo(PlanoVersao::class, 'plano_versao_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}