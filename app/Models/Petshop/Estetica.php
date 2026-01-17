<?php

namespace App\Models\Petshop;

use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\Funcionario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Plano;
use App\Models\Servico;
use App\Models\Petshop\EsteticaServico;
use App\Models\Petshop\EsteticaProduto;
use App\Models\OrdemServico;

class Estetica extends Model
{
    use HasFactory;

    protected $table = 'petshop_esteticas';

    protected $fillable = [
        'empresa_id',
        'animal_id',
        'cliente_id',
        'colaborador_id',
        'servico_id',
        'plano_id',
        'ordem_servico_id',
        'descricao',
        'valor',
        'data_agendamento',
        'horario_agendamento',
        'horario_saida',
        'estado',
    ];

    protected $casts = [
        'data_agendamento' => 'date',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public static function statusEstetica()
    {
        return [
            'agendado' => 'Agendado',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'rejeitado' => 'Rejeitado',
            'pendente_aprovacao' => 'Aprovação pendente',
        ];
    }

    public static function getStatusEstetica(string $status) 
    {
        return self::class::statusEstetica()[$status];
    }

    public static function statusEsteticaForOrdemServico()
    {
        return [
            'agendado' => 'AG',
            'em_andamento' => 'EA',
            'concluido' => 'FZ',
            'rejeitado' => 'RJ',
            'cancelado' => 'CC',
        ];
    }

    public static function getStatusEsteticaForOrdemServico(string $status) 
    {
        return self::class::statusEsteticaForOrdemServico()[$status];
    }

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function colaborador()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function servicos()
    {
        return $this->hasMany(EsteticaServico::class);
    }

    public function esteticaClienteEndereco()
    {
        return $this->hasOne(EsteticaClienteEndereco::class, 'estetica_id');
    }

    public function produtos()
    {
        return $this->hasMany(EsteticaProduto::class);
    }

    public function contaReceber()
    {
        return $this->hasOne(ContaReceber::class, 'estetica_id', 'id');
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }
}
