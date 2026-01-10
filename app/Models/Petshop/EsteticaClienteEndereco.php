<?php

namespace App\Models\Petshop;

use App\Models\Cidade;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EsteticaClienteEndereco extends Model
{
    use HasFactory;

    protected $table = 'esteticas_clientes_enderecos';

    protected $fillable = [
        'estetica_id',
        'cliente_id',
        'cidade_id',
        'cep',
        'rua',
        'bairro',
        'numero',
        'complemento'
    ];

    protected $appends = ['endereco_url'];

    public function getEnderecoUrlAttribute()
    {
        $endereco_parts = array_filter([
            $this->rua,
            $this->numero,
            $this->bairro,
            $this->cidade->nome ?? null,
        ]);

        $endereco = implode(', ', $endereco_parts);

        $endereco_encoded = urlencode($endereco);

        return "https://www.google.com/maps/search/?api=1&query={$endereco_encoded}";
    }

    public function estetica()
    {
        return $this->belongsTo(Estetica::class, 'estetica_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }
}
