<?php

namespace App\Models\Petshop;

use App\Models\Servico;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanoServico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'petshop_plano_servicos';

    protected $fillable = [
        'plano_versao_id',
        'servico_id',
        'qtd_por_ciclo',
        'valor_servico',
        'coparticipacao_tipo',
        'coparticipacao_valor',
    ];

    public function versao()
    {
        return $this->belongsTo(PlanoVersao::class, 'plano_versao_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}