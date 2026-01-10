<?php

namespace App\Models\Petshop;

use App\Models\Empresa;
use App\Models\Petshop\Especie;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacina extends Model
{
    use HasFactory;

    protected $table = 'petshop_vet_vacinas';

    protected $fillable = [
        'empresa_id',
        'produto_id',
        'codigo',
        'nome',
        'status',
        'grupo_vacinal',
        'categoria',
        'fabricante',
        'registro_mapa',
        'apresentacao',
        'concentracao',
        'idade_minima',
        'intervalo_reforco',
        'dosagem',
        'via_administracao',
        'local_aplicacao',
        'coberturas',
        'protocolo_inicial',
        'protocolo_reforco',
        'protocolo_revacinar',
        'requisitos_pre_vacinacao',
        'orientacoes_pos_vacinacao',
        'efeitos_adversos',
        'contraindicacoes',
        'validade_fechada',
        'validade_aberta',
        'condicao_armazenamento',
        'temperatura_armazenamento',
        'alertas_armazenamento',
        'limite_perdas',
        'tempo_reposicao',
        'documentos',
        'tags',
        'observacoes',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'produto_id' => 'integer',
        'documentos' => 'array',
        'tags' => 'array',
    ];

    private const STATUS = [
        'ativa' => ['label' => 'Ativa', 'color' => 'success'],
        'inativa' => ['label' => 'Inativa', 'color' => 'secondary'],
        'em_revisao' => ['label' => 'Em revisão', 'color' => 'warning'],
    ];

    private const GRUPOS = [
        'essenciais_caes' => 'Essenciais - Cães',
        'essenciais_gatos' => 'Essenciais - Gatos',
        'obrigatorias' => 'Obrigatórias',
        'recomendadas' => 'Recomendadas / Regionais',
        'preventivos' => 'Preventivos complementares',
    ];

    private const CATEGORIAS = [
        'polivalente' => 'Polivalente',
        'antirrabica' => 'Antirrábica',
        'leptospirose' => 'Leptospirose',
        'gripe_canina' => 'Gripe Canina',
        'felina' => 'Protocolos Felinos',
        'outros' => 'Outros / Especialidades',
    ];

    private const FABRICANTES = [
        'vetpharma' => 'VetPharma',
        'biovet' => 'BioVet',
        'petcare_labs' => 'PetCare Labs',
        'zoetis' => 'Zoetis',
        'bayer' => 'Bayer Saúde Animal',
    ];

    private const APRESENTACOES = [
        'unidosis' => 'Unidose (1 dose por frasco)',
        'multidose_5' => 'Multidose 5 doses',
        'multidose_10' => 'Multidose 10 doses',
        'liofilizado' => 'Liofilizado com diluente',
    ];

    private const VIAS_ADMINISTRACAO = [
        'subcutanea' => 'Subcutânea',
        'intramuscular' => 'Intramuscular',
        'intranasal' => 'Intranasal',
        'oral' => 'Oral',
    ];

    private const LOCAIS_APLICACAO = [
        'escapula_direita' => 'Escápula direita',
        'escapula_esquerda' => 'Escápula esquerda',
        'quadriceps' => 'Músculo quadríceps',
        'membro_posterior' => 'Membro posterior',
        'intranasal' => 'Aplicação intranasal',
    ];

    private const IDADES_MINIMAS = [
        'filhotes_6_semanas' => 'Filhotes (≥ 6 semanas)',
        'filhotes_8_semanas' => 'Filhotes (≥ 8 semanas)',
        'filhotes_12_semanas' => 'Filhotes (≥ 12 semanas)',
        'adultos' => 'Adultos',
        'senior' => 'Sênior (avaliar caso a caso)',
    ];

    private const INTERVALOS_REFORCO = [
        'anual' => 'Anual (12 meses)',
        'bienal' => 'Bienal (24 meses)',
        'trienal' => 'Trienal (36 meses)',
        'semestral' => 'Semestral',
        'conforme_protocolo' => 'Conforme protocolo do fabricante',
    ];

    private const CONDICOES_ARMAZENAMENTO = [
        'cadeia_fria' => 'Cadeia fria 2ºC a 8ºC',
        'refrigerada' => 'Refrigerada 4ºC a 8ºC',
        'temperatura_controlada' => 'Temperatura controlada 8ºC a 15ºC',
        'ambiente' => 'Temperatura ambiente (15ºC a 25ºC)',
    ];

    private const DOCUMENTOS = [
        'carteira_vacinacao' => 'Carteira de vacinação',
        'termo_consentimento' => 'Termo de consentimento',
        'folha_monitoramento' => 'Folha de monitoramento pós-vacinal',
        'declaracao_aplicacao' => 'Declaração de aplicação',
        'orientacao_tutor' => 'Orientações impressas para o tutor',
    ];

    public static function opcoesStatus()
    {
        return array_map(fn(array $dados) => $dados['label'], self::STATUS);
    }

    public static function dadosStatus(string $status)
    {
        return self::STATUS[$status] ?? ['label' => ucfirst($status), 'color' => 'secondary'];
    }

    public static function opcoesGrupos()
    {
        return self::GRUPOS;
    }

    public static function opcoesCategorias()
    {
        return self::CATEGORIAS;
    }

    public static function opcoesFabricantes()
    {
        return self::FABRICANTES;
    }

    public static function opcoesApresentacoes()
    {
        return self::APRESENTACOES;
    }

    public static function opcoesViasAdministracao()
    {
        return self::VIAS_ADMINISTRACAO;
    }

    public static function opcoesLocaisAplicacao()
    {
        return self::LOCAIS_APLICACAO;
    }

    public static function opcoesIdadesMinimas()
    {
        return self::IDADES_MINIMAS;
    }

    public static function opcoesIntervalosReforco()
    {
        return self::INTERVALOS_REFORCO;
    }

    public static function opcoesCondicoesArmazenamento()
    {
        return self::CONDICOES_ARMAZENAMENTO;
    }

    public static function opcoesDocumentos()
    {
        return self::DOCUMENTOS;
    }

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
            'petshop_vet_vacina_especies',
            'vacina_id',
            'especie_id'
        )->withTimestamps();
    }
}