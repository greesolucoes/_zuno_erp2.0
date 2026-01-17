import { Abertura, Caixa, CaixaFisico, Categoria, Funcionario, Local, User, Vendedor } from "./company";
import { FormaPagamentoDetalhe, TiposPagamento } from "./payment";
import { Venda } from "./product";
export interface Cidade {
    nome?: string;
    uf?: string;
}
export type TTeclasAtalhos =
    | 'fluxo_diario'
    | 'comanda'
    | 'devolucao'
    | 'trocas'
    | 'venda_consignada'
    | 'pre_venda'
    | 'abrir_gaveta'
    | 'suspender_vendas'
    | 'vendas_suspensas'
    | 'resumo_de_vendas'
    | 'convenios_e_limite'
    | 'cancelar_cupom'
    | 'consultar_cashback'
    | 'recarga_celular'
    | 'reimpressao_de_nfc_e'
    | 'suprimento'
    | 'sangria'
    | 'ordem_servico'
    | 'scanner_produtos'
    | 'pagamentos_ordem_servico'
    | 'fechar_caixa';
export interface ResponseData {
    errors: Record<string, unknown>;
    categorias: Categoria[];
    abertura: Abertura;
    funcionarios: Funcionario[];
    caixa: Caixa;
    tiposPagamento: TiposPagamento;
    item: Venda[] | null; // pode ser ajustado se houver detalhes sobre `item`
    isVendaSuspensa: number;
    title: string;
    configTef: unknown; // pode ser ajustado se houver detalhes sobre `configTef`
    marcas: Array<unknown>; // pode ser ajustado se houver detalhes sobre a estrutura de `marcas`
    produtos: Array<unknown>; // pode ser ajustado se houver detalhes sobre a estrutura de `produtos`
    operador_nome: string;
    locais: Local[];
    user: User;
    localAtivo: number;
    cidade?: Cidade;
    config?: { [key: string]: any; vendedor_obrigatorio?: number };
    vendedores?: Vendedor[];
    teclas_atalhos?: Array<TTeclasAtalhos>;
    bandeiras?: TiposPagamento;
    detalhesFormas?: FormaPagamentoDetalhe[];
    caixa_fisico: CaixaFisico;
    local: Local;
    banner?: string;
}
export interface FinishedOS {
    id: number;
    codigo_sequencial: number;
    cliente: string;
    valor_total: number;
    data_finalizada: string;
    funcionario: string;
    has_produto: boolean;
    has_servico: boolean;
}
