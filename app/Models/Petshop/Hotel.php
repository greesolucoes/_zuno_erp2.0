<?php

namespace App\Models\Petshop;

use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\Servico;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hotel extends Model
{
    use HasFactory;

    protected $table = 'hoteis';

    protected $fillable = [
        'empresa_id',
        'animal_id',
        'cliente_id',
        'quarto_id',
        'servico_id',
        'colaborador_id',
        'plano_id',
        'ordem_servico_id',
        'diarias',
        'descricao',
        'checkin',
        'checkout',
        'valor',
        'estado',
        'situacao_checklist',
    ];

    protected $casts = [
        'checkin' => 'datetime',
        'checkout' => 'datetime',
        'situacao_checklist' => 'boolean',
    ];

    public static function statusHotel()
    {
        return [
            'agendado' => 'Agendado',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído',
            'rejeitado' => 'Rejeitado',
            'cancelado' => 'Cancelado',
        ];
    }

    public static function getStatusHotel(string $status) 
    {
        return self::class::statusHotel()[$status];
    }

    public static function statusHotelForOrdemServico()
    {
        return [
            'agendado' => 'AG',
            'em_andamento' => 'EA',
            'concluido' => 'FZ',
            'rejeitado' => 'RJ',
            'cancelado' => 'CC',
        ];
    }

    public static function getStatusHotelForOrdemServico(string $status) 
    {
        return self::class::statusHotelForOrdemServico()[$status];
    }

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

    public function quarto()
    {
        return $this->belongsTo(Quarto::class, 'quarto_id');
    }

    public function colaborador()
    {
        return $this->belongsTo(Funcionario::class, 'colaborador_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }
    public function servicos()
    {
        return $this->belongsToMany(Servico::class, 'hotel_servico', 'hotel_id', 'servico_id')
            ->withPivot(['data_servico', 'hora_servico', 'valor_servico']);
    }

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'hotel_produto', 'hotel_id', 'produto_id')
            ->withPivot('quantidade');
    }

    public function hotelClienteEndereco()
    {
        return $this->hasOne(HotelClienteEndereco::class, 'hotel_id');
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function contaReceber()
    {
        return $this->hasOne(ContaReceber::class);
    }

    public function checklists()
    {
        return $this->hasMany(HotelChecklist::class, 'hotel_id');
    }
}
