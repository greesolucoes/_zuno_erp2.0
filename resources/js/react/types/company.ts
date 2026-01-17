export interface Categoria {
    id: number;
    empresa_id: number;
    nome: string;
    nome_en: string | null;
    nome_es: string | null;
    cardapio: number;
    delivery: number;
    ecommerce: number;
    reserva: number;
    tipo_pizza: number;
    hash_ecommerce: string | null;
    hash_delivery: string | null;
    categoria_id: number | null;
    created_at: string;
    updated_at: string;
}
export interface Abertura {
    id: number;
    empresa_id: number;
    usuario_id: number;
    valor_abertura: string;
    conta_empresa_id: number | null;
    observacao: string;
    status: number;
    data_fechamento: string | null;
    valor_fechamento: string;
    valor_dinheiro: string | null;
    valor_cheque: string | null;
    valor_outros: string | null;
    local_id: number;
    created_at: string;
    updated_at: string;
}
export interface Funcionario {
    id: number;
    empresa_id: number;
    nome: string;
    cpf_cnpj: string | null;
    telefone: string | null;
    cidade_id: number;
    rua: string | null;
    cep: string | null;
    numero: string | null;
    bairro: string | null;
    usuario_id: number;
    comissao: string;
    comissao_percent: string | null;
    salario: string;
    codigo: string | null;
    created_at: string;
    updated_at: string;
    cargo_id: number;
}
export interface Localizacao {
    id: number;
    empresa_id: number;
    descricao: string;
    status: number;
    nome: string;
    nome_fantasia: string;
    cpf_cnpj: string;
    aut_xml: string | null;
    ie: string;
    email: string | null;
    celular: string | null;
    arquivo: string | null;
    senha: string | null;
    cep: string;
    rua: string;
    numero: string;
    bairro: string;
    complemento: string | null;
    cidade_id: number;
    numero_ultima_nfe_producao: string | null;
    numero_ultima_nfe_homologacao: string | null;
    numero_serie_nfe: string | null;
    numero_ultima_nfce_producao: string | null;
    numero_ultima_nfce_homologacao: string | null;
    numero_serie_nfce: string | null;
    numero_ultima_cte_producao: string | null;
    numero_ultima_cte_homologacao: string | null;
    numero_serie_cte: string | null;
    numero_ultima_mdfe_producao: string | null;
    numero_ultima_mdfe_homologacao: string | null;
    numero_serie_mdfe: string | null;
    numero_ultima_nfse: string | null;
    numero_ultima_nfse_homologacao: string | null;
    numero_serie_nfse: string | null;
    csc: string | null;
    csc_id: string | null;
    ambiente: number;
    tributacao: string;
    token_nfse: string | null;
    logo: string;
    created_at: string;
    updated_at: string;
}
export interface Caixa {
    id: number;
    empresa_id: number;
    usuario_id: number;
    valor_abertura: string;
    conta_empresa_id: number | null;
    observacao: string;
    status: number;
    data_fechamento: string | null;
    valor_fechamento: string;
    valor_dinheiro: number | null;
    valor_cheque: number | null;
    valor_outros: number | null;
    local_id: number;
    created_at: string;
    updated_at: string;
    localizacao: Localizacao;
}

export interface CaixaFisico {
    id: number;
    empresa_id: number;
    local_id: number;
    descricao: string;
    ativo: boolean;
    acesso_todas_formas_pagamentos: boolean;
}
export interface Local {
    id: number;
    empresa_id: number;
    descricao: string;
    status: number;
    nome: string;
    nome_fantasia: string;
    cpf_cnpj: string;
    aut_xml: string | null;
    ie: string | null;
    email: string | null;
    celular: string | null;
    arquivo: string | null;
    senha: string | null;
    cep: string | null;
    rua: string;
    numero: string | null;
    bairro: string;
    complemento: string | null;
    cidade_id: number;
    numero_ultima_nfe_producao: string | null;
    numero_ultima_nfe_homologacao: string | null;
    numero_serie_nfe: string | null;
    numero_ultima_nfce_producao: string | null;
    numero_ultima_nfce_homologacao: string | null;
    numero_serie_nfce: string | null;
    numero_ultima_cte_producao: string | null;
    numero_ultima_cte_homologacao: string | null;
    numero_serie_cte: string | null;
    numero_ultima_mdfe_producao: string | null;
    numero_ultima_mdfe_homologacao: string | null;
    numero_serie_mdfe: string | null;
    numero_ultima_nfse: string | null;
    numero_ultima_nfse_homologacao: string | null;
    numero_serie_nfse: string | null;
    csc: string | null;
    csc_id: string | null;
    ambiente: number;
    tributacao: string;
    token_nfse: string | null;
    logo: string;
    created_at: string;
    updated_at: string;
}
// Interface para o objeto "EmpresaBase" aninhado na propriedade "empresa" dentro de "empresa"
export interface EmpresaBase {
    id: number;
    nome: string;
    nome_fantasia: string;
    razao_social?: string;
    cpf_cnpj: string;
    nao_fiscal?: boolean;
    aut_xml: string | null;
    ie: string;
    email: string | null;
    celular: string | null;
    senha: string | null;
    status: number;
    cep: string;
    rua: string;
    numero: string;
    bairro: string;
    complemento: string | null;
    cidade_id: number;
    natureza_id_pdv: number;
    numero_ultima_nfe_producao: string | null;
    numero_ultima_nfe_homologacao: string | null;
    numero_serie_nfe: string | null;
    numero_ultima_nfce_producao: string | null;
    numero_ultima_nfce_homologacao: string | null;
    numero_serie_nfce: string | null;
    numero_ultima_cte_producao: string | null;
    numero_ultima_cte_homologacao: string | null;
    numero_serie_cte: string | null;
    numero_ultima_mdfe_producao: string | null;
    numero_ultima_mdfe_homologacao: string | null;
    numero_serie_mdfe: string | null;
    numero_ultima_nfse: string | null;
    numero_ultima_nfse_homologacao: string | null;
    numero_serie_nfse: string | null;
    csc: string;
    csc_id: string;
    ambiente: number;
    tributacao: string;
    token: string | null;
    token_nfse: string | null;
    logo: string;
    tipo_contador: number;
    limite_cadastro_empresas: number;
    percentual_comissao: string;
    empresa_selecionada: string | null;
    exclusao_icms_pis_cofins: number;
    observacao_padrao_nfe: string;
    observacao_padrao_nfce: string;
    created_at: string;
    updated_at: string;
    info: string;
    plano?: {
        plano?: {
            segmento?: {
                nome?: string;
            };
        };
    };
}

export interface Empresa {
    id: number;
    empresa_id: number;
    usuario_id: number;
    created_at: string;
    updated_at: string;
    empresa: EmpresaBase;
}
// Interface para "Pivot" dentro de "roles"
export interface Pivot {
    model_type: string;
    model_id: number;
    role_id: number;
}
export interface Role {
    id: number;
    name: string;
    description: string;
    guard_name: string;
    empresa_id: number;
    is_default: number;
    type_user: number;
    created_at: string;
    updated_at: string;
    pivot: Pivot;
}
// Interface principal para o objeto "User"
export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    imagem: string;
    admin: number;
    sidebar_active: number;
    notificacao_cardapio: number;
    notificacao_marketplace: number;
    notificacao_ecommerce: number;
    tipo_contador: number;
    created_at: string;
    updated_at: string;
    empresa: Empresa;
    permissions: Array<unknown>; // Pode ser ajustado se a estrutura de "permissions" for conhecida
    roles: Role[];
}
export interface Cliente {
    value: number; // Valor único representando o cliente
    label: string; // Concatenado da razão social e CPF/CNPJ para exibição

    id: number; // ID do cliente
    empresa_id: number; // ID da empresa associada ao cliente
    razao_social: string; // Razão social do cliente
    nome_fantasia: string; // Nome fantasia do cliente
    cpf_cnpj: string; // CPF ou CNPJ do cliente
    ie: string | null; // Inscrição Estadual (pode ser nulo)
    contribuinte: number; // Indica se o cliente é contribuinte
    consumidor_final: number; // Indica se o cliente é consumidor final
    email: string; // E-mail do cliente
    telefone: string; // Telefone principal
    cidade_id: number; // ID da cidade
    rua: string; // Nome da rua do cliente
    cep: string; // CEP do cliente
    numero: string; // Número da residência ou endereço
    bairro: string; // Bairro do cliente
    complemento: string | null; // Complemento do endereço (pode ser nulo)
    status: number; // Status do cliente
    token: string | null; // Token associado (pode ser nulo)
    uid: string | null; // UID único do cliente (pode ser nulo)
    senha: string | null; // Senha do cliente (pode ser nulo)
    valor_cashback: string; // Valor de cashback do cliente
    valor_credito: string; // Valor de crédito do cliente
    nuvem_shop_id: number | null; // ID na Nuvem Shop (pode ser nulo)
    limite_credito: string | null; // Limite de crédito (pode ser nulo)
    created_at: string; // Data de criação
    updated_at: string; // Data de atualização
    telefone_secundario: string | null; // Segundo telefone do cliente (pode ser nulo)
    telefone_terciario: string | null; // Terceiro telefone do cliente (pode ser nulo)
    endereco: string; // Endereço completo formatado
    info: string; // Informação adicional formatada do cliente
}
export interface Vendedor {
    id: number;
    nome: string;
    cargo: string;
    cpf_cnpj: string | null;
    codigo: string;
    usuario_id: number;
    cidade_id: number;
}
export interface ListPrice {
    id: number;
    empresa_id: number;
    nome: string;
    ajuste_sobre: string;
    tipo: string;
    percentual_alteracao: string;
    tipo_pagamento: string;
    funcionario_id: number;
    created_at: string;
    updated_at: string;
    label?: string;
    value?: number;
}
