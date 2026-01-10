<?php

namespace App\Models\petshop;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Pedido;
use App\Models\Servico;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Escola extends Model
{
    use HasFactory;

    protected $table = 'escolas';

    protected $fillable = [
        'empresa_id',
        'animal_id',
        'cliente_id',
        'colaborador_id',
        'sala_de_aula_id',
        'pedido_id',
        'servico_id',
        'descricao',
        'checkin',
        'checkout',
        'valor',
        'estado',
    ];

    protected $casts = [
        'checkin' => 'datetime',
        'checkout' => 'datetime',
    ];

    // Relacionamentos

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function colaborador()
    {
        return $this->belongsTo(Funcionario::class, 'colaborador_id');
    }

    public function sala()
    {
        return $this->belongsTo(SalaDeAula::class, 'sala_de_aula_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}