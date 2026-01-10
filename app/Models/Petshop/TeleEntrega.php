<?php

namespace App\Models\Petshop;

use App\Models\Cidade;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeleEntrega extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tele_entregas';

    protected $fillable = [
        'empresa_id',  'cliente_id', 'tipo_id', 'datahora_entrega', 'valor', 'status', 'observacao', 'rua',
        'numero', 'cep', 'bairro', 'cidade_id', 'complemento', 'motorista_nome', 'foi_pago'
    ];

    public function cliente(){
      return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function tipo(){
      return $this->belongsTo(TipoTeleEntrega::class, 'tipo_id');
    }

    public function cidade(){
      return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public static function status(){
        return [
            'pendente' => 'Pendente',
            'entregue' => 'Entregue',
            'cancelado' => 'Cancelado',
        ];
    }

    public function getFoiPago()
    {
      $foiPago = $this->attributes['foi_pago'] ?? null;

      if ($foiPago == 1) {
        return 'Sim';
      }

      return 'Não';
    }

}
