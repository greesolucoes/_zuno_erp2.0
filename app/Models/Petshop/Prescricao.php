<?php

namespace App\Models\Petshop;

use App\Models\Empresa;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Alergia;
use App\Models\Petshop\CondicaoCronica;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Medico;
use App\Models\Petshop\ModeloPrescricao;
use App\Models\Petshop\PrescricaoCanal;
use App\Models\Petshop\PrescricaoMedicamento;
use App\Models\Petshop\Prontuario;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescricao extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_prescricoes';

    protected $fillable = [
        'empresa_id',
        'animal_id',
        'veterinario_id',
        'atendimento_id',
        'prontuario_id',
        'modelo_prescricao_id',
        'diagnostico',
        'resumo',
        'observacoes',
        'orientacoes',
        'dispensacao_id',
        'dispensacao_observacoes',
        'campos_personalizados',
        'emitida_em',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'animal_id' => 'integer',
        'veterinario_id' => 'integer',
        'atendimento_id' => 'integer',
        'prontuario_id' => 'integer',
        'modelo_prescricao_id' => 'integer',
        'emitida_em' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'campos_personalizados' => 'array',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function veterinario()
    {
        return $this->belongsTo(Medico::class, 'veterinario_id');
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id');
    }

    public function prontuario()
    {
        return $this->belongsTo(Prontuario::class, 'prontuario_id');
    }

    public function modelo()
    {
        return $this->belongsTo(ModeloPrescricao::class, 'modelo_prescricao_id');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function atualizador()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function medicamentos()
    {
        return $this->hasMany(PrescricaoMedicamento::class, 'prescricao_id');
    }

    public function canais()
    {
        return $this->hasMany(PrescricaoCanal::class, 'prescricao_id');
    }

    public function alergias()
    {
        return $this->belongsToMany(Alergia::class, 'petshop_vet_prescricao_alergia', 'prescricao_id', 'alergia_id')->withTimestamps();
    }

    public function condicoes()
    {
        return $this->belongsToMany(CondicaoCronica::class, 'petshop_vet_prescricao_condicao_cronica', 'prescricao_id', 'condicao_cronica_id')->withTimestamps();
    }
}