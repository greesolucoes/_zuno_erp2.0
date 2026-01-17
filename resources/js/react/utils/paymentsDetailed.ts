/*  ==========================================================
 *  buildPaymentsDetailed.ts – versão com “bruto limpo”
 *  ========================================================== */

import { MultiPayment, FormaPagamentoDetalhe } from '../types';
import type { IStore } from '../store';

/* ---------- Dinheiro em centavos ---------- */
type Money = bigint;
const toMoney  = (v: number): Money  => BigInt(Math.round(v * 100));
const toNumber = (m: Money): number  => Number(m) / 100;

const add = (a: Money, b: Money) => a + b;
const sub = (a: Money, b: Money) => a - b;

function allocate(total: Money, pesos: Money[]): Money[] {
    if (pesos.length === 0) return [];
    if (total === 0n || pesos.every(p => p === 0n)) {
        return pesos.map(() => 0n);
    }
    const soma  = pesos.reduce((s, p) => s + p, 0n);          // > 0n
    const base  = pesos.map(p => (total * p) / soma);
    let resto   = total - base.reduce((s, p) => s + p, 0n);   // 0 ≤ resto < soma

    for (let i = 0; resto > 0n; i = (i + 1) % base.length) {
        base[i] += 1n;
        resto--;
    }
    return base;
}

const splitEvenly = (t: Money, n: number) =>
    allocate(t, Array.from({ length: n }, () => 1n));

/* ---------- Tipos de saída ---------- */
export interface PaymentInstallment {
    parcela: number;
    valor_bruto: number;
    valor_liquido: number;
    vencimento: string;
}

export interface PaymentDetailed {
    forma_pagamento_id?: number;
    tipo_pagamento: string;
    condicao_pagamento: 'avista' | 'parcelado';
    numero_parcelas: number;
    valor_bruto: number;
    valor_desconto: number;
    valor_acrescimo: number;
    valor_liquido: number;
    /** @deprecated */
    valor: number;
    data_vencimento: string;
    bandeira_cartao?: string;
    cAut_cartao?: string;
    cnpj_cartao?: string;
    parcelas?: PaymentInstallment[];
}

interface BuildArgs {
    payments: MultiPayment[];
    detalhesMap: Map<string, FormaPagamentoDetalhe>;
    cardDetails?: IStore['store']['card_details'];
    acrescimo: number;
    desconto: number;
    descontoTipo: '%' | 'R$';
    acrescimoFP: string[];
    descontoFP: string[];
}

/* ===================== Função principal ===================== */
export function buildPaymentsDetailed({
                                          payments,
                                          detalhesMap,
                                          cardDetails,
                                          acrescimo,
                                          desconto,
                                          descontoTipo,
                                          acrescimoFP,
                                          descontoFP,
                                      }: BuildArgs): PaymentDetailed[] {

    /* Agrupamento por forma de pagamento ---------------------- */
    const groups = new Map<string, MultiPayment[]>();
    payments.forEach(p => {
        const lst = groups.get(p.payment_method) || [];
        lst.push(p); groups.set(p.payment_method, lst);
    });

    /* Totais globais ------------------------------------------ */
    const totalGeral = payments.map(p => toMoney(p.value)).reduce(add, 0n);
    const descTotal  = descontoTipo === 'R$'
        ? toMoney(desconto)
        : (totalGeral * BigInt(Math.round(desconto * 100))) / 10_000n;
    const acresTotal = toMoney(acrescimo);

    /* Alvos de desconto / acréscimo --------------------------- */
    const alvoDesc   = descontoFP.length ? descontoFP : [...groups.keys()];
    const alvoAcresc = acrescimoFP.length ? acrescimoFP : [...groups.keys()];

    /* Pesos por método ---------------------------------------- */
    const peso: Record<string, Money> = {};
    groups.forEach((list, m) => {
        peso[m] = list.map(p => toMoney(p.value)).reduce(add, 0n);
    });

    /* Rateio de ajustes --------------------------------------- */
    const getPeso = (m: string): Money => peso[m] ?? 0n;

    const descArr = allocate(descTotal,  alvoDesc  .map(getPeso));
    const acreArr = allocate(acresTotal, alvoAcresc.map(getPeso));
    const descMap:  Record<string, Money> = {};
    const acreMap:  Record<string, Money> = {};
    alvoDesc.forEach((m, i)   => { descMap[m]  = descArr[i]; });
    alvoAcresc.forEach((m, i) => { acreMap[m]  = acreArr[i]; });

    /* Helper de datas ---------------------------------------- */
    const addMonths = (base: string, m: number) => {
        const d = new Date(base); d.setMonth(d.getMonth() + m);
        return d.toISOString().split('T')[0];
    };

    const result: PaymentDetailed[] = [];

    /* Processa cada forma ------------------------------------ */
    groups.forEach((list, method) => {
        const pago      = list.map(p => toMoney(p.value)).reduce(add, 0n);
        const descMet   = descMap[method]  ?? 0n;
        const acreMet   = acreMap[method]  ?? 0n;

        /* === REGRAS NOVAS ===================================== */
        const bruto   = add(sub(pago, acreMet), descMet);   // valor sem ajustes
        const liquido = pago;                                 // o que entrou no caixa

        /* Parcelas --------------------------------------------- */
        const nParcelas = method === '03'
            ? cardDetails?.repeat || list.length
            : list.length;

        const condicao = nParcelas > 1 ? 'parcelado' : 'avista';
        const first    = list[0];

        let parcelas: PaymentInstallment[] | undefined;

        if (list.length > 1) {                             // parcelas explícitas
            const pagosParc = list.map(p => toMoney(p.value));
            const acreParc  = allocate(acreMet, pagosParc);

            parcelas = list.map((p, i) => ({
                parcela:       i + 1,
                valor_bruto:   toNumber(sub(pagosParc[i], acreParc[i])),
                valor_liquido: toNumber(pagosParc[i]),
                vencimento:    p.expiration_date,
            }));
        } else if (nParcelas > 1) {                        // parcelas virtuais
            const brutos   = splitEvenly(bruto,   nParcelas);
            const liquidos = splitEvenly(liquido, nParcelas);
            parcelas = brutos.map((b, i) => ({
                parcela:       i + 1,
                valor_bruto:   toNumber(b),
                valor_liquido: toNumber(liquidos[i]),
                vencimento:    addMonths(first.expiration_date, i),
            }));
        }

        /* Invariante: bruto - desconto + acréscimo = líquido ---- */
        if (bruto - descMet + acreMet !== liquido)
            throw new Error(`invariante quebrada em ${method}`);

        /* DTO final -------------------------------------------- */
        const det = detalhesMap.get(method);
        result.push({
            forma_pagamento_id: det?.id,
            tipo_pagamento:     method,
            condicao_pagamento: condicao,
            numero_parcelas:    nParcelas,
            valor_bruto:        toNumber(bruto),
            valor_desconto:     toNumber(descMet),
            valor_acrescimo:    toNumber(acreMet),
            valor_liquido:      toNumber(liquido),
            valor:              toNumber(liquido),          // legado
            data_vencimento:    first.expiration_date,
            bandeira_cartao:    method === '03' ? cardDetails?.card_flag  : undefined,
            cAut_cartao:        method === '03' ? cardDetails?.card_cvv   : undefined,
            cnpj_cartao:        method === '03' ? cardDetails?.doc_number : undefined,
            ...(parcelas && { parcelas }),
        });
    });

    return result;
}
