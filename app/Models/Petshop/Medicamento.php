<?php

namespace App\Models\Petshop;

use App\Models\Empresa;
use App\Models\Petshop\Especie;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_medicamentos';

    protected $fillable = [
        'empresa_id',
        'produto_id',
        'nome_comercial',
        'nome_generico',
        'classe_terapeutica',
        'classe_farmacologica',
        'classificacao_controle',
        'via_administracao',
        'apresentacao',
        'concentracao',
        'forma_dispensacao',
        'dosagem',
        'frequencia',
        'duracao',
        'restricao_idade',
        'condicao_armazenamento',
        'validade',
        'fornecedor',
        'sku',
        'indicacoes',
        'contraindicacoes',
        'efeitos_adversos',
        'interacoes',
        'monitoramento',
        'orientacoes_tutor',
        'observacoes',
        'status',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'produto_id' => 'integer',
    ];

    private const CATEGORIAS_TERAPEUTICAS = [
        'Analgesia e controle da dor',
        'Anestésicos',
        'Antibióticos',
        'Antifúngicos',
        'Antiparasitários',
        'Anti-inflamatórios',
        'Antieméticos e antiespasmódicos',
        'Cardiológicos',
        'Dermatológicos',
        'Endócrinos e hormonais',
        'Gastrointestinais',
        'Neurológicos e anticonvulsivantes',
        'Nutracêuticos e suplementos',
        'Oftálmicos',
        'Otológicos',
        'Renais e urinários',
        'Respiratórios',
        'Sedação e ansiolíticos',
        'Suporte intensivo e emergência',
        'Outra categoria (especificar nas observações)',
    ];

    private const VIAS_ADMINISTRACAO = [
        'Oral',
        'Subcutânea',
        'Intramuscular',
        'Intravenosa',
        'Intradérmica',
        'Tópica',
        'Transdérmica',
        'Oftálmica',
        'Otológica',
        'Inalatória',
        'Retal',
        'Sublingual',
        'Outra via (descrever nas observações)',
    ];

    private const APRESENTACOES = [
        'Comprimido',
        'Comprimido mastigável',
        'Cápsula',
        'Solução oral',
        'Suspensão oral',
        'Xarope',
        'Gotas orais',
        'Pó para reconstituição',
        'Frasco injetável',
        'Seringa pronta para uso',
        'Sachê',
        'Gel',
        'Pomada',
        'Spray',
        'Shampoo terapêutico',
        'Colírio',
        'Óleo ou solução otológica',
        'Pastilha ou snack funcional',
        'Outra apresentação (descrever nas observações)',
    ];

    private const FORMAS_DISPENSACAO = [
        'Embalagem original fechada',
        'Fracionado por unidade',
        'Fracionado por volume (mL)',
        'Fracionado por peso (g)',
        'Manipulado sob prescrição',
        'Dose única administrada na consulta',
        'Uso exclusivo em ambiente clínico',
        'Controle especial com retenção de receita',
        'Outra forma de dispensação (descrever nas observações)',
    ];

    private const RESTRICOES_IDADE = [
        'Sem restrição (uso geral)',
        'Filhotes até 3 meses',
        'Filhotes de 3 a 6 meses',
        'Jovens de 6 a 12 meses',
        'Adultos',
        'Sênior (acima de 7 anos)',
        'Somente animais acima de 2 kg',
        'Somente animais acima de 5 kg',
        'Contraindicado para gestantes e lactantes',
        'Usar com cautela em pacientes geriátricos',
        'Outra restrição etária (descrever nas observações)',
    ];

    private const CONDICOES_ARMAZENAMENTO = [
        'Temperatura ambiente controlada (15°C a 25°C)',
        'Temperatura ambiente com proteção da luz',
        'Refrigerado (2°C a 8°C)',
        'Congelado (-20°C ou abaixo)',
        'Não congelar',
        'Proteger da umidade',
        'Armazenar em geladeira após aberto',
        'Uso imediato após reconstituição',
        'Manter em embalagem original fechada',
        'Outra condição de armazenamento (descrever nas observações)',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function especies()
    {
        return $this->belongsToMany(
            Especie::class,
            'petshop_vet_medicamento_especies',
            'medicamento_id',
            'especie_id'
        )->withTimestamps();
    }

    public static function opcoesCategoriasTerapeuticas()
    {
        return self::formatarOpcoes(self::CATEGORIAS_TERAPEUTICAS);
    }

    public static function opcoesViasAdministracao()
    {
        return self::formatarOpcoes(self::VIAS_ADMINISTRACAO);
    }

    public static function opcoesApresentacoes()
    {
        return self::formatarOpcoes(self::APRESENTACOES);
    }

    public static function opcoesFormasDispensacao()
    {
        return self::formatarOpcoes(self::FORMAS_DISPENSACAO);
    }

    public static function opcoesRestricoesIdade()
    {
        return self::formatarOpcoes(self::RESTRICOES_IDADE);
    }

    public static function opcoesCondicoesArmazenamento()
    {
        return self::formatarOpcoes(self::CONDICOES_ARMAZENAMENTO);
    }

    private static function formatarOpcoes(array $valores)
    {
        $opcoes = array_combine($valores, $valores);

        $opcoes['__custom__'] = 'Personalizado';

        return $opcoes;
    }
}