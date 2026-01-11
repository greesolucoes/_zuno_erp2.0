<?php

namespace App\Models\Petshop;

use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\Produto;
use App\Models\Servico;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creche extends Model
{
    use HasFactory;

    protected $table = 'petshop_creches';

    protected $fillable = [
        'empresa_id',
        'animal_id',
        'cliente_id',
        'turma_id',
        'colaborador_id',
        'ordem_servico_id',
        'descricao',
        'valor',
        'data_entrada',
        'data_saida',
        'estado',
        'situacao_checklist',
    ];

    protected $casts = [
        'data' => 'date',
        'situacao_checklist' => 'boolean',
    ];

    public static function statusCreche()
    {
        return [
            'agendado' => 'Agendado',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído',
            'rejeitado' => 'Rejeitado',
            'cancelado' => 'Cancelado',
        ];
    }

    public static function getStatusCreche(string $status) 
    {
        return self::class::statusCreche()[$status];
    }

    public static function statusCrecheForOrdemServico()
    {
        return [
            'agendado' => 'AG',
            'em_andamento' => 'EA',
            'concluido' => 'FZ',
            'rejeitado' => 'RJ',
            'cancelado' => 'CC',
        ];
    }

    public static function getStatusCrecheForOrdemServico(string $status) 
    {
        return self::class::statusCrecheForOrdemServico()[$status];
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

    public function turma()
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function colaborador()
    {
        return $this->belongsTo(Funcionario::class, 'colaborador_id');
    }

    public function servicos()
    {
        return $this->belongsToMany(Servico::class, 'petshop_creche_servico', 'creche_id', 'servico_id')
            ->withPivot(['data_servico', 'hora_servico', 'valor_servico']);
    }

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'petshop_creche_produto', 'creche_id', 'produto_id')
            ->withPivot('quantidade');
    }

    public function crecheClienteEndereco()
    {
        return $this->hasOne(CrecheClienteEndereco::class, 'creche_id');
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
        return $this->hasMany(CrecheChecklist::class, 'creche_id');
    }
}
