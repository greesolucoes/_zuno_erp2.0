/* eslint-disable react-hooks/exhaustive-deps */
import {
    FC,
    PropsWithChildren,
    useCallback,
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';
import { useDispatch, useSelector } from 'react-redux';
import InputCurrency from '../../components/shared/InputCurrency';
import {
    convertBrazilianCurrencyToFloat,
    formatToCurrency,
} from '../../helpers';
import { usePaymentChoice } from '../../hooks/usePaymentChoice';
import { emitirFiscal } from '../../services/emissaoFiscalService';
import { finalizarVenda } from '../../services/finalizarVendaService';
import { IStore } from '../../store';
import {
    calculateGlobalTotal,
    finishPage,
    getPayloadToSuspendSale,
    handleDiscount,
    setAcrescimo,
    setMultiPayment,
    setOrdemServicoHasProduto,
    setOrdemServicoHasServico,
    setOrdemServicoId,
    setPaymentUrl,
} from '../../store/slices/pdvSlice';
import '../../style/style.css';
import {
    FormaPagamentoDetalhe,
    MultiPayment,
    TiposPagamento,
} from '../../types';
import { updatePaymentButtonsDisabledState } from '../../utils/paymentButtons';
import { buildPaymentsDetailed } from '../../utils/paymentsDetailed';
import { resetPdv } from '../../utils/resetPdv';
import { openChangeConfirmation } from './modals';
console.log('Finish page loaded global style.css');

/* -------------------------------------------------------------------------- */
/*  COMPONENTE                                                                */
/* -------------------------------------------------------------------------- */

const Finish: FC<
    PropsWithChildren<{
        tiposPagamento: TiposPagamento;
        empresa_id: number;
        usuario_id: number;
        isNaoFiscal?: boolean;
        url?: string;
        detalhesFormas: FormaPagamentoDetalhe[];
        ordem_servico_id?: number;
        caixa_id?: number;
        ordem_servico_has_produto?: boolean;
        ordem_servico_has_servico?: boolean;
    }>
> = ({
         children,
         tiposPagamento,
         usuario_id,
         empresa_id,
         isNaoFiscal = false,
         url = 'api/frenteCaixa/store',
         detalhesFormas,
         ordem_servico_id,
         caixa_id,
         ordem_servico_has_produto,
         ordem_servico_has_servico,
     }) => {
            const isServiceOrderPayment = Boolean(ordem_servico_id);

    /* ---------------------------------------------------------------------- */
    /*  STATES                                                                */
    /* ---------------------------------------------------------------------- */
    const [current, setCurrent] = useState<MultiPayment>();
    const [currentDetail, setCurrentDetail] = useState<FormaPagamentoDetalhe>();
    const [currentValue, setCurrentValue] = useState('');

    const multiPayment = useSelector(
        (state: IStore) => state.store.multiPayment || [],
    );
    const [payments, setPayments] = useState<MultiPayment[]>(multiPayment);

    const desconto = useSelector((state: IStore) => state.store.desconto || 0);
    const acrescimo = useSelector((state: IStore) => state.store.acrescimo || 0);
    const descontoTipo = useSelector((state: IStore) => state.store.desconto_tipo);
    const descontoFP = useSelector(
        (state: IStore) => state.store.desconto_forma_pagamento || [],
    );
    const acrescimoFP = useSelector(
        (state: IStore) => state.store.acrescimo_forma_pagamento || [],
    );
    const cardDetails = useSelector(
        (state: IStore) => state.store.card_details,
    );
    const total = useSelector(calculateGlobalTotal);

    const [sold, setSold] = useState(false);
    const [gerouNota, setGerouNota] = useState(false);
    const [gerouNotaFiscal, setGerouNotaFiscal] = useState(false);
    const [selectingFiscalType, setSelectingFiscalType] = useState(false);
    const [selectingNonFiscalType, setSelectingNonFiscalType] = useState(false);
    const [gerouComprovante, setGerouComprovante] = useState(false);
    const [sale, setSale] = useState<
        { [key: string]: string | number; id: string | number } | undefined
    >();

    const dispatch = useDispatch();

    const manualDiscount = useRef(desconto);
    const manualAcrescimo = useRef(acrescimo);

    /* ---------------------------------------------------------------------- */
    /*  MEMOS                                                                 */
    /* ---------------------------------------------------------------------- */
    const totalRecebido = useMemo(
        () => payments.reduce((acc, { value }) => acc + value, 0),
        [payments],
    );
      const remainingAmount = useMemo(
        () => Number((total - totalRecebido).toFixed(2)),
        [total, totalRecebido],
    );
    const detalhesMap = useMemo(() => {
        const m = new Map<string, FormaPagamentoDetalhe>();
        detalhesFormas.forEach((f) => m.set(f.tipo_pagamento, f));
        return m;
    }, [detalhesFormas]);

    const pagamentosDetalhados = useMemo(
        () =>
            buildPaymentsDetailed({
                payments,
                detalhesMap,
                cardDetails,
                acrescimo,
                desconto,
                descontoTipo,
                acrescimoFP,
                descontoFP,
            }),
        [
            payments,
            detalhesMap,
            cardDetails,
            acrescimo,
            desconto,
            descontoTipo,
            acrescimoFP,
            descontoFP,
        ],
    );

    const recalcAdjustments = useCallback(
        (currentPays: MultiPayment[] = payments) => {
            let autoDesc = 0;
            let autoAcres = 0;
            currentPays.forEach(({ payment_method, value, extra = 0 }) => {
                const det = detalhesMap.get(payment_method);
                if (!det) return;

                const applyDesc = descontoFP.includes(payment_method);
                const applyAcres = acrescimoFP.includes(payment_method);
                const descPct = applyDesc ? det.desconto_automatico || 0 : 0;
                const acrePct = applyAcres ? det.acrescimo_automatico || 0 : 0;

                let base = value;

                if (extra) {
                    autoAcres += extra;
                    if (descPct) {
                        base = (value - extra) / (1 - descPct / 100);
                    } else {
                        return; // only extra was applied
                    }
                } else if (descPct || acrePct) {
                    const factor = 1 + (acrePct - descPct) / 100;
                    base = value / factor;
                    autoAcres += (base * acrePct) / 100;
                }

                if (descPct) {
                    autoDesc += (base * descPct) / 100;
                }
            });
            dispatch(
                handleDiscount(Number((manualDiscount.current + autoDesc).toFixed(2)))
            );
            dispatch(
                setAcrescimo(Number((manualAcrescimo.current + autoAcres).toFixed(2)))
            );
        },
        [payments, detalhesMap, descontoFP, acrescimoFP, dispatch]
    );

    const choosePayment = usePaymentChoice({
        pagamentosAtuais: payments,
        total,
        detalhesMap,
        currentValue,
        empresa_id,
        dispatch,
        setCurrent,
        setCurrentDetail,
        setCurrentValue,
        setPayments,
        totalRecebido,
    });

    useEffect(() => {
        recalcAdjustments(payments);
    }, [payments, recalcAdjustments]);

    /* ---------------------------------------------------------------------- */
    /*  EFFECTS                                                               */
    /* ---------------------------------------------------------------------- */
    useEffect(() => {
        updatePaymentButtonsDisabledState(payments, total, totalRecebido);

        if (payments.length) {
            const paymentsLabels = payments.map(({ payment_label }) =>
                payment_label?.toLowerCase(),
            );

            for (const button of document.querySelectorAll(
                '.forma_pagamento',
            ) as unknown as HTMLButtonElement[]) {
                if (paymentsLabels.includes(button.innerText.toLowerCase())) {
                    button.classList.add('active');
                    button.disabled = false;
                }
            }
        }
    }, []); // apenas on-mount

    useEffect(() => {
        dispatch(setMultiPayment(payments));
    }, [payments, dispatch]);

    /* ---------------------------------------------------------------------- */
    /*  CONSTANTES AUXILIARES                                                 */
    /* ---------------------------------------------------------------------- */
    const salePayload = useSelector(
        getPayloadToSuspendSale(empresa_id, usuario_id, total),
    );

    const showFiscal = true;
    const showNonFiscal = true;
    const fiscalBlocked = Boolean(isNaoFiscal);
    const fiscalTooltip =
        ordem_servico_id &&
        ordem_servico_has_produto &&
        ordem_servico_has_servico
            ? 'Only the products on this Service Order will be included in the NFC-e. To issue the invoice for the service(s), please use the dedicated NFSe screen.'
            : undefined;
    const fiscalButtonTitle = fiscalBlocked
        ? 'Empresa configurada como Não Fiscal.'
        : fiscalTooltip;

    /* ---------------------------------------------------------------------- */
    /*  RENDER                                                                */
    /* ---------------------------------------------------------------------- */
    return (
        <main id="finish">
            {children}

            <section>
                {/* -------------------------------------------------------------- */}
                {/*  CAIXA DA ESQUERDA – ETAPAS 1 e 2                              */}
                {/* -------------------------------------------------------------- */}
                {!gerouNota && (
                    <div className="box_wrap">
                        {/* -------------------------------------------------- */}
                        {/*  PASSO 2 – CUPOM / NOTA                           */}
                        {/* -------------------------------------------------- */}
                        {sold ? (
                            selectingFiscalType ? (
                                /* ---------------------------------------------- */
                                /*  2.1 – Escolha NF-e x NFS-e                    */
                                /* ---------------------------------------------- */
                                <div id="fiscal-type">
                                    <h2>Escolha o tipo de nota fiscal</h2>

                                    <div className="d-flex gap-4 mt-4">
                                        {/* NFSe ----------------------------------- */}
                                        <button
                                            type="button"
                                            className="btn-fiscal flex-grow-1"
                                            disabled={fiscalBlocked}
                                            title={fiscalBlocked ? 'Empresa configurada como Não Fiscal.' : undefined}
                                            onClick={async () => {
                                                if (fiscalBlocked) return;
                                                if (!sale) return;

                                                const success = await emitirFiscal(String(sale.id), {
                                                    empresa_id,
                                                    ordem_servico_id,
                                                    ordem_servico_has_produto: false,
                                                    ordem_servico_has_servico: true,
                                                    redirectOnError: !ordem_servico_id,

                                                });

                                                if (success) {
                                                    /* NÃO AVANÇA AINDA – apenas habilita */
                                                    setGerouNotaFiscal(true);
                                                }
                                            }}
                                        >
                                            NFS-e
                                        </button>

                                        {/* NFC-e ---------------------------------- */}
                                        <button
                                            type="button"
                                            className="btn-fiscal flex-grow-1"
                                            disabled={fiscalBlocked}
                                            title={fiscalBlocked ? 'Empresa configurada como Não Fiscal.' : undefined}
                                            onClick={async () => {
                                                if (fiscalBlocked) return;
                                                if (!sale) return;

                                                const success = await emitirFiscal(String(sale.id), {
                                                    empresa_id,
                                                    ordem_servico_id,
                                                    ordem_servico_has_produto: true,
                                                    ordem_servico_has_servico: false,
                                                    redirectOnError: !ordem_servico_id,
                                                });

                                                if (success) {
                                                    /* NÃO AVANÇA AINDA – apenas habilita */
                                                    setGerouNotaFiscal(true);
                                                }
                                            }}
                                        >
                                            NFC-e
                                        </button>
                                    </div>
                                </div>
                            ) : selectingNonFiscalType ? (
                                /* ---------------------------------------------- */
                                /*  2.2 – Escolha do Comprovante                 */
                                /* ---------------------------------------------- */
                                <div id="fiscal-type">
                                    <h2>Escolha o tipo de comprovante</h2>

                                    <div className="d-flex gap-4 mt-4">
                                        <button
                                            type="button"
                                            className="btn-imprimir-os flex-grow-1"
                                            onClick={() => {
                                                if (!sale) return;
                                                window.open(`/ordem-servico/imprimir/${sale.id}`, '_blank');
                                                setGerouComprovante(true);
                                            }}
                                        >
                                            {/* comprovante já existente da ordem de serviço */}
                                            Comprovante O.S
                                        </button>

                                        <button
                                            type="button"
                                            className="btn-nao-fiscal flex-grow-1"
                                            onClick={() => {
                                                if (!sale) return;
                                                window.open(`/ordem-servico/imprimir-comprovante/${sale.id}`, '_blank');
                                                setGerouComprovante(true);
                                            }}
                                        >
                                            {/* comprovante de venda não fiscal existente */}
                                            Comprovante de Venda
                                        </button>
                                    </div>
                                </div>
                            ) : (
                                /* ---------------------------------------------- */
                                /*  2.0 – Cupom Fiscal / Não Fiscal              */
                                /* ---------------------------------------------- */
                                <div id="print">
                                    <h2>Escolha seu cupom</h2>
                                    <p>Para realizar a impressão</p>

                                    <div className="w-100 d-flex align-items-center gap-4 mt-4">
                                        {/* ---------- Cupom Fiscal ---------- */}
                                        {showFiscal && (
                                            <button
                                                type="button"
                                                disabled={fiscalBlocked}
                                                title={fiscalButtonTitle}
                                                onClick={() =>
                                                    fiscalBlocked ? undefined : finalizarVenda({
                                                        url,
                                                        salePayload: {
                                                            ...salePayload,
                                                            pagamentos_detalhados: JSON.stringify(pagamentosDetalhados),
                                                        },
                                                        usuario_id,
                                                        ordem_servico_id,
                                                        caixa_id,
                                                        sale,
                                                        setSale,
                                                        ordem_servico_has_produto,
                                                        cpf_na_nota: true,
                                                        onSuccess: async (saleId: string) => {
                                                            if (
                                                                ordem_servico_id &&
                                                                ordem_servico_has_produto &&
                                                                ordem_servico_has_servico
                                                            ) {
                                                                if (fiscalBlocked) return;
                                                                setSelectingFiscalType(true);
                                                            } else {
                                                                const ok = await emitirFiscal(saleId, {
                                                                    empresa_id,
                                                                    ordem_servico_id,
                                                                    ordem_servico_has_produto,
                                                                    ordem_servico_has_servico,
                                                                    redirectOnError: !ordem_servico_id,
                                                                });

                                                                if (ok) {
                                                                    setGerouNota(true);
                                                                    setGerouNotaFiscal(true);
                                                                }
                                                            }
                                                        },
                                                    })
                                                }
                                            >
                                                Fiscal
                                            </button>
                                        )}

                                        {/* -------- Cupom Não Fiscal -------- */}
                                        {showNonFiscal && (
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    finalizarVenda({
                                                        url,
                                                        salePayload: {
                                                            ...salePayload,
                                                            pagamentos_detalhados: JSON.stringify(pagamentosDetalhados),
                                                        },
                                                        usuario_id,
                                                        ordem_servico_id,
                                                        caixa_id,
                                                        sale,
                                                        setSale,
                                                        ordem_servico_has_produto,
                                                        onSuccess: (saleId: string) => {
                                                            if (ordem_servico_id) {
                                                                setSelectingNonFiscalType(true);
                                                            } else {
                                                                window.open(
                                                                    `/frenteCaixa/imprimir-nao-fiscal/${saleId}`,
                                                                    '_blank',
                                                                );
                                                                setGerouNota(true);
                                                            }
                                                        },
                                                    });
                                                }}
                                            >
                                                Não Fiscal
                                            </button>
                                        )}
                                    </div>
                                </div>
                            )
                        ) : (
                            /* -------------------------------------------------- */
                            /*  PASSO 1 – PAGAMENTO                              */
                            /* -------------------------------------------------- */
                            <>
                                <h2 className="titulo text-center">Efetuar Pagamento</h2>

                                {/* Restante a pagar */}
                                <div id="restante">
                                    <p id="restante__titulo">Restante</p>
                                    <p id="restante__valor">
                                        {total - totalRecebido >= 0
                                            ? formatToCurrency(total - totalRecebido)
                                            : '-'}
                                    </p>
                                </div>

                                {/* Desconto / Acréscimo */}
                                <div id="desconto_acrescimo">
                                    <div id="desconto">
                                        <p id="desconto__label" style={{ marginRight: 10 }}>
                                            Desconto:
                                        </p>
                                        <div className="d-flex flex-row align-items-end justify-content-center">
                                            <InputCurrency
                                                id="desconto_valor"
                                                className="monetary border-0"
                                                placeholder=""
                                                defaultValue={desconto.toFixed(2)}
                                                min={0}
                                                max={total}
                                                onBlur={(value) => {
                                                    manualDiscount.current = isNaN(parseFloat(value)) ? 0 : parseFloat(value);
                                                    recalcAdjustments();
                                                }}
                                                disabled={
                                                    current !== undefined ||
                                                    isServiceOrderPayment
                                                }
                                            />
                                        </div>
                                    </div>

                                    <div id="acrescimo">
                                        <p id="acrescimo__label" style={{ marginRight: 10 }}>
                                            Acréscimo:
                                        </p>
                                        <div className="d-flex flex-row align-items-end justify-content-center">
                                            <InputCurrency
                                                id="valor_acrescimo"
                                                className="monetary border-0"
                                                placeholder=""
                                                defaultValue={acrescimo.toFixed(2)}
                                                min={0}
                                                max={100}
                                                onBlur={(value) => {
                                                    manualAcrescimo.current = isNaN(parseFloat(value)) ? 0 : parseFloat(value);
                                                    recalcAdjustments();
                                                }}
                                                disabled={
                                                    current !== undefined
                                                }
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Valor recebido */}
                                <div id="recebido">
                                    <p id="recebido__titulo">Recebido:</p>
                                    <InputCurrency
                                        id="valor_recebido"
                                        className="monetary border-0"
                                        placeholder=""
                                        min={0}
                                        onChange={(value) => {
                                            setCurrentValue(value);
                                            updatePaymentButtonsDisabledState(
                                                payments,
                                                total,
                                                totalRecebido,
                                            );
                                        }}
                                        defaultValue={convertBrazilianCurrencyToFloat(currentValue)}
                                        disabled={total - totalRecebido == 0 ? true : false}
                                    />
                                </div>

                                {/* Botões de formas de pagamento */}
                                <div id="formas_pagamento">
                                    {Object.entries(tiposPagamento).map(([key, value], index) => (
                                        <button
                                            key={index}
                                            type="button"
                                            className="btn forma_pagamento border-0 text-light relative"
                                            onClick={choosePayment(
                                                key,
                                                value.replaceAll(
                                                    'Pagamento Instantâneo (PIX) ',
                                                    'PIX',
                                                ),
                                            )}
                                        >
                                            <span id="close">X</span>
                                            {value.replaceAll('Pagamento Instantâneo (PIX) ', 'PIX')}
                                        </button>
                                    ))}
                                </div>
                            </>
                        )}
                    </div>
                )}

                {/* -------------------------------------------------------------- */}
                {/*  CAIXA DA DIREITA – RESUMO E AÇÕES                            */}
                {/* -------------------------------------------------------------- */}
                <div className="box_wrap resume">
                    <h2 className="titulo text-center">Pagamentos Realizados</h2>

                    <div id="pagamentos">
                        {payments.length > 0 ? (
                            <>
                                {payments.map((payment, index) => (
                                    <div key={index} className="pagamento">
                                        <p>+ {payment.payment_label}</p>
                                        <p>{formatToCurrency(payment.value)}</p>
                                    </div>
                                ))}
                            </>
                        ) : (
                            <div className="d-flex align-items-center pagamento">
                                <p className="w-100 fw-lighter text-center">
                                    Selecione uma forma de pagamento
                                </p>
                            </div>
                        )}
                    </div>

                    <h2 className="text-center mb-2">Resumo da Venda</h2>

                    <div id="resumo">
                        <div id="valor_total">
                            <p id="valor_total__label">Valor Total</p>
                            <p id="valor_total__valor">{formatToCurrency(total)}</p>
                        </div>

                        <div id="total_acrescimo">
                            <p>Acréscimo</p>
                            <p>+ {formatToCurrency(acrescimo)}</p>
                        </div>

                        <div id="total_desconto">
                            <p>Desconto</p>
                            <p>- {formatToCurrency(desconto)}</p>
                        </div>

                        <div id="total_recebido">
                            <p id="total_recebido__label">Total Recebido:</p>
                            <p id="total_recebido__valor">
                                {formatToCurrency(totalRecebido)}
                            </p>
                        </div>
                    </div>

                    {/* -------------------------------------------------- */}
                    {/*  BOTÕES DE AÇÃO                                   */}
                    {/* -------------------------------------------------- */}
                    <div className="d-flex flex-row justify-content-center gap-3 mt-4">
                        {selectingFiscalType || selectingNonFiscalType ? (
                            /* --------- CONTINUAR (verde) --------- */
                            <button
                                id="continuar"
                                className="btn btn-success px-5 py-3 fw-bold"
                                disabled={selectingFiscalType ? !gerouNotaFiscal : !gerouComprovante}
                                onClick={() => {
                                    setGerouNota(true); // avança de vez
                                    if (selectingFiscalType) setSelectingFiscalType(false);
                                    if (selectingNonFiscalType) setSelectingNonFiscalType(false);
                                }}
                            >
                                CONTINUAR
                            </button>
                        ) : (
                            <>
                                {/* Troco (quando há) */}
                                {totalRecebido - total > 0 &&
                                    payments.length === 1 &&
                                    payments[0].payment_method === '01' && (
                                        <h3 id="troco" className="p-4">
                                            Troco
                                            <small className="text-light ms-2">
                                                {formatToCurrency(totalRecebido - total)}
                                            </small>
                                        </h3>
                                    )}

                                {/* Pagar */}
                                {!sold && (
                                    <button
                                        type="button"
                                        id="pagar"
                                       disabled={remainingAmount > 0} 
                                       onClick={async () => {
                                            if (
                                                totalRecebido - total > 0 &&
                                                payments.length === 1 &&
                                                payments[0].payment_method === '01'
                                            ) {
                                                const confirmed = await openChangeConfirmation(
                                                    totalRecebido - total,
                                                );
                                                if (confirmed) {
                                                    setSold(true);
                                                }
                                            } else {
                                                setSold(true);
                                            }
                                        }}
                                    >
                                        PAGAR
                                    </button>
                                )}

                                {/* Fechar / Voltar */}
                                {gerouNota && !selectingFiscalType && !selectingNonFiscalType ? (
                                    <button
                                        type="button"
                                        id="fechar"
                                        onClick={() => {
                                            if (sold) {
                                                resetPdv(dispatch);
                                            } else {
                                                dispatch(finishPage(false));
                                                dispatch(setPaymentUrl('api/frenteCaixa/store'));
                                                dispatch(setOrdemServicoId(undefined));
                                                dispatch(setOrdemServicoHasProduto(undefined));
                                                dispatch(setOrdemServicoHasServico(undefined));
                                            }
                                        }}
                                    >
                                        Fechar
                                    </button>
                                ) : (
                                    !sold && (
                                        <button
                                            type="button"
                                            id="voltar"
                                            onClick={() => dispatch(finishPage(false))}
                                        >
                                            Voltar
                                        </button>
                                    )
                                )}
                            </>
                        )}
                    </div>
                </div>
            </section>
        </main>
    );
};

export default Finish;
