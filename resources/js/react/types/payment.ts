export interface TiposPagamento {
    [key: string]: string;
}
export interface MultiPayment {
    payment_method: string;
    expiration_date: string;
    value: number;
    /** Valor extra aplicado ao pagamento (juros, acréscimos etc.) */
    extra?: number;
    description?: string;
    payment_label?: string;
}

export interface FormaPagamentoDetalhe {
    id: number;
    nome: string;
    tipo_pagamento: string;
    valor_minimo: number;
    desconto_automatico: number;
    acrescimo_automatico: number;
    limite_parcelas_sem_acrescimo: number;
    acrescimo_parcelamento: number;
    parcelas_disponiveis: number[];
    dias_vencimento: number;
    multa: number;
    valor_final: number | null;
    valores_parcelas: number[];
    deve_registrar_conta_receber: boolean;
}