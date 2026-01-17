import { useCallback, useEffect, useMemo, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { AxiosError, isAxiosError } from 'axios';
import Swal from 'sweetalert2';
import { axiosClient, Modal } from '../../../constants';
import {
    appendLoadingElement,
    convertBrazilianCurrencyToFloat,
    formatToCurrency,
    removeLoadingElement,
} from '../../../helpers';
import { IStore } from '../../../store';
import {
    calculateGlobalTotal,
    finishPage,
    getPayloadToSuspendSale,
    handleDiscount,
    openClose,
    resetState,
    setAcrescimo,
    addDescontoFormaPagamento,
    removeDescontoFormaPagamento,
    addAcrescimoFormaPagamento,
    removeAcrescimoFormaPagamento,
    setCardDetails,
    setMultiPayment,
    setClient,
    setPaymentUrl,
    setOrdemServicoId,
    setOrdemServicoHasProduto,
    setOrdemServicoHasServico,
} from '../../../store/slices/pdvSlice';
import {
    MultiPayment,
    FormaPagamentoDetalhe,
    TiposPagamento,
} from '../../../types';
import {
    openCreditCardModal,
    openCrediarioModal,
    openCpfNotaModal,
    openOverpaymentWarning,
    openMinimumValueWarning,
    openSaleRegisteredModal,
    openSaleErrorModal,
} from '../modals';
import { buildPaymentsDetailed } from '../../../utils/paymentsDetailed';

export interface UseFinishProps {
    tiposPagamento: TiposPagamento;
    empresa_id: number;
    usuario_id: number;
    url?: string;
    detalhesFormas: FormaPagamentoDetalhe[];
    ordem_servico_id?: number;
    caixa_id?: number;
    ordem_servico_has_produto?: boolean;
    ordem_servico_has_servico?: boolean;
}

export default function useFinish({
                                      tiposPagamento,
                                      empresa_id,
                                      usuario_id,
                                      url = 'api/frenteCaixa/store',
                                      detalhesFormas,
                                      ordem_servico_id,
                                      caixa_id,
                                      ordem_servico_has_produto,
                                      ordem_servico_has_servico,
                                  }: UseFinishProps) {
    const [current, setCurrent] = useState<MultiPayment | undefined>();
    const [currentDetail, setCurrentDetail] = useState<FormaPagamentoDetalhe | undefined>();
    const [currentValue, setCurrentValue] = useState<string>('');
    const multiPayment = useSelector((state: IStore) => state.store.multiPayment || []);
    const [payments, setPayments] = useState<MultiPayment[]>(multiPayment);

    const dispatch = useDispatch();
    const totalRecebido = useMemo(() => payments.reduce((acc, { value }) => acc + value, 0), [payments]);
    const desconto = useSelector((state: IStore) => state?.store?.desconto || 0);
    const acrescimo = useSelector((state: IStore) => state?.store?.acrescimo || 0);
    const descontoTipo = useSelector((state: IStore) => state.store.desconto_tipo);
    const descontoFP = useSelector(
        (state: IStore) => state.store.desconto_forma_pagamento || [],
    );
    const acrescimoFP = useSelector(
        (state: IStore) => state.store.acrescimo_forma_pagamento || [],
    );
    const cardDetails = useSelector((state: IStore) => state.store.card_details);
    const total = useSelector(calculateGlobalTotal);
    const [sold, setSold] = useState(false);
    const [gerouNota, setGerouNota] = useState(false);
    const [gerouNotaFiscal, setGerouNotaFiscal] = useState(false);
    const [sale, setSale] = useState<{ [key: string]: string | number; id: string | number }>();

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

    const updateDisabled = useCallback((currentPayments: MultiPayment[] | false = false) => {
        const pays = currentPayments !== false ? currentPayments : payments;
        const valor_recebido_input = document.getElementById('valor_recebido') as HTMLInputElement;
        if (valor_recebido_input) {
            const valor_recebido = convertBrazilianCurrencyToFloat(valor_recebido_input.value);
            for (const button of document.querySelectorAll('.forma_pagamento') as unknown as HTMLButtonElement[]) {
                const classList = [...button.classList];
                if (
                    button.innerText.toLowerCase().includes('cartão') ||
                    button.innerText.toLowerCase().includes('cartao') ||
                    button.innerText.toLowerCase().includes('pix')
                ) {
                    if (total !== totalRecebido && valor_recebido) {
                        button.disabled = false;
                    }
                    if (!pays.length) {
                        button.disabled = false;
                    } else {
                        button.disabled =
                            (!!pays.length && !valor_recebido && !classList.includes('active')) ||
                            (!!pays.length && total === totalRecebido && !classList.includes('active'));
                    }
                } else {
                    button.disabled = !valor_recebido && !classList.includes('active');
                }
            }
        }
    }, [payments, total, totalRecebido]);

    const handleValueChange = useCallback(
        (value: string) => {
            setCurrentValue(value);
            updateDisabled();
        },
        [updateDisabled],
    );

    const handleDescontoBlur = useCallback(
        (value: string) => {
            const parsed = convertBrazilianCurrencyToFloat(value);
            dispatch(handleDiscount(isNaN(parsed) ? 0 : parsed));
        },
        [dispatch],
    );

    const handleAcrescimoBlur = useCallback(
        (value: string) => {
            const parsed = convertBrazilianCurrencyToFloat(value);
            dispatch(setAcrescimo(isNaN(parsed) ? 0 : parsed));
        },
        [dispatch],
    );

    const choosePayment = useCallback(
        (key: string, value: string) => async (e: React.MouseEvent<HTMLButtonElement>) => {
            const target = e.currentTarget;
            const classList = [...target.classList];
            const isRemove = classList.includes('active');
            const valor_recebido_input = document.getElementById('valor_recebido') as HTMLInputElement;
            let isOk = true;
            const detCurrent = detalhesMap.get(key);
            let parcelas = 1;
            let extra = 0;
            let valorFinal = 0;
            if (!isRemove && key == '03') {
                const baseValue =
                    !payments.length &&
                    (value.toLowerCase().includes('cartão') ||
                        value.toLowerCase().includes('cartao') ||
                        value.toLowerCase().includes('pix')) &&
                    !convertBrazilianCurrencyToFloat(currentValue)
                        ? total
                        : convertBrazilianCurrencyToFloat(currentValue);
                const result = await openCreditCardModal(detCurrent, baseValue);
                isOk = result.isConfirmed;
                parcelas = result.vezes;
                extra = result.extra;
                valorFinal = result.valorFinal;
                if (result.isConfirmed) {
                    dispatch(setCardDetails({ repeat: Number(result.vezes) }));
                }
            }
            if (!isRemove && key == '06') {
                const baseValue =
                    !payments.length && !convertBrazilianCurrencyToFloat(currentValue)
                        ? total
                        : convertBrazilianCurrencyToFloat(currentValue);
                const result = await openCrediarioModal(detCurrent, empresa_id, baseValue);
                if (result) {
                    parcelas = result.parcelas;
                    const valorParcela = baseValue / parcelas;
                    const pagamentos = result.vencimentos.map((v, i) => ({
                        description: `Parcela ${i + 1}/${parcelas}`,
                        expiration_date: v,
                        value: valorParcela,
                        payment_label: value,
                        payment_method: key,
                    }));
                    dispatch(setClient(result.cliente));
                    setPayments((prev) => {
                        const currentPays = [...prev, ...pagamentos];
                        setCurrentValue('');
                        updateDisabled(currentPays);
                        return currentPays;
                    });
                    target.classList.add('active');
                    isOk = false; // avoid default addition below
                }
            }
            let baseInput =
                !payments.length &&
                (value.toLowerCase().includes('cartão') ||
                    value.toLowerCase().includes('cartao') ||
                    value.toLowerCase().includes('pix')) &&
                !convertBrazilianCurrencyToFloat(currentValue)
                    ? total
                    : convertBrazilianCurrencyToFloat(currentValue);
            if (key !== '01') {
                const predictedTotal = total + (extra || 0);
                const recebido = totalRecebido + (valorFinal || baseInput);
                if (recebido > predictedTotal) {
                    isOk = false;
                    await openOverpaymentWarning();
                    setCurrentValue('');
                    valor_recebido_input.value = '';
                    valor_recebido_input.focus();
                }
            }
            if (!isRemove && isOk && detCurrent && detCurrent.valor_minimo > 0) {
                if (convertBrazilianCurrencyToFloat(currentValue) < detCurrent.valor_minimo) {
                    isOk = false;
                    await openMinimumValueWarning(detCurrent.valor_minimo);
                }
            }
            if (isOk) {
                target.classList.toggle('active');

                if (isRemove) {
                    setPayments((prev) => {
                        const currentPays = prev.filter(({ payment_method }) => payment_method !== key);
                        setCurrent(undefined);
                        setCurrentDetail(undefined);
                        setCurrentValue('');
                        valor_recebido_input.value = '';
                        valor_recebido_input.focus();
                        updateDisabled(currentPays);
                        dispatch(handleDiscount(0));
                        dispatch(setAcrescimo(0));
                        dispatch(removeDescontoFormaPagamento(key));
                        dispatch(removeAcrescimoFormaPagamento(key));
                        return currentPays;
                    });
                } else {
                    valor_recebido_input.value = '';

                    const now = new Date();
                    let paymentAmount = baseInput;
                    let extraValue = extra || 0;
                    let discountValue = 0;
                    if (!extraValue && detCurrent?.acrescimo_automatico) {
                        extraValue =
                            (baseInput * detCurrent.acrescimo_automatico) / 100;
                    }
                    if (detCurrent?.desconto_automatico) {
                        discountValue =
                            (baseInput * detCurrent.desconto_automatico) / 100;
                    }
                    if (detCurrent && (extraValue || discountValue)) {
                        paymentAmount = baseInput + extraValue - discountValue;
                    }
                    const currentPayment = {
                        description: '',
                        expiration_date: now.toISOString().split('T')[0],
                        value: paymentAmount,
                        payment_label: value,
                        payment_method: key,
                        extra: extraValue || undefined,
                    } as MultiPayment;
                    setCurrent(currentPayment);
                    setCurrentDetail(detalhesMap.get(key));
                    const det = detalhesMap.get(key);
                    if (det) {
                        if (discountValue) {
                            dispatch(handleDiscount(discountValue));
                            dispatch(addDescontoFormaPagamento(key));
                        }
                        let computedExtra = extraValue;
                        if (
                            !computedExtra &&
                            det.acrescimo_automatico &&
                            baseInput
                        ) {
                            computedExtra =
                                (baseInput * det.acrescimo_automatico) / 100;
                        }
                        if (computedExtra) {
                            dispatch(setAcrescimo(computedExtra));
                            dispatch(addAcrescimoFormaPagamento(key));
                        }
                    }
                    setPayments((prev) => {
                        const currentPays = [...prev, currentPayment];
                        setCurrentValue('');
                        updateDisabled(currentPays);
                        return currentPays;
                    });
                }
            }
        },
        [payments, currentValue, current, updateDisabled, totalRecebido, total, detalhesMap, empresa_id],
    );

    useEffect(() => {
        updateDisabled();
        if (payments.length) {
            const paymentsLabels = payments.map(({ payment_label }) => payment_label?.toLowerCase());
            for (const button of document.querySelectorAll('.forma_pagamento') as unknown as HTMLButtonElement[]) {
                if (paymentsLabels.includes(button.innerText.toLowerCase())) {
                    button.classList.add('active');
                    button.disabled = false;
                }
            }
        }
    }, []);

    useEffect(() => {
        dispatch(setMultiPayment(payments));
    }, [payments, dispatch]);

    const salePayload = useSelector(getPayloadToSuspendSale(empresa_id, usuario_id, total));

    const finalizarVenda = async (
        callbackFinish: (saleId: string) => void,
        cpf_na_nota: boolean = false,
    ) => {
        try {
            let othersFields = {} as any;
            let currentSaleId = sale?.id;
            if (cpf_na_nota) {
                const result = await openCpfNotaModal();
                if (result) {
                    othersFields = {
                        cliente_cpf_cnpj: result.cpf_cnpj,
                        cliente_nome: result.nome,
                    };
                }
            }

            if (!sale) {
                const { data } = await axiosClient.post(url, {
                    ...salePayload,
                    pagamentos_detalhados: JSON.stringify(pagamentosDetalhados),
                    ...othersFields,
                    usuario_id,
                    ...(ordem_servico_id ? { ordem_servico_id } : {}),
                    ...(caixa_id ? { caixa_id } : {}),
                });
                setSale(data);
                currentSaleId = data.id;
            }
            await openSaleRegisteredModal(
                ordem_servico_id && !ordem_servico_has_produto,
            );
            await callbackFinish(currentSaleId as any);
        } catch (e: unknown | AxiosError) {
            await openSaleErrorModal((e as any).response?.data);
            console.error(e);
        }
    };

    const close = useCallback(() => {
        if (sold) {
            dispatch(resetState());
            dispatch(setPaymentUrl('api/frenteCaixa/store'));
            dispatch(setOrdemServicoId(undefined));
            dispatch(setOrdemServicoHasProduto(undefined));
            dispatch(setOrdemServicoHasServico(undefined));
            const $body = document.body;
            $body.classList.add('loading');
            appendLoadingElement();
            setTimeout(() => {
                dispatch(openClose(true));
                $body.classList.remove('loading');
                removeLoadingElement();
            }, 1000);
        } else {
            dispatch(finishPage(false));
            dispatch(setPaymentUrl('api/frenteCaixa/store'));
            dispatch(setOrdemServicoId(undefined));
            dispatch(setOrdemServicoHasProduto(undefined));
            dispatch(setOrdemServicoHasServico(undefined));
        }
    }, [sold, dispatch]);

    const goBack = useCallback(() => {
        dispatch(finishPage(false));
    }, [dispatch]);

    const showFiscal = true;
    const showNonFiscal = true;
    const fiscalTooltip =
        ordem_servico_id &&
        ordem_servico_has_produto &&
        ordem_servico_has_servico
            ? 'Only the products on this Service Order will be included in the NFC-e. To issue the invoice for the service(s), please use the dedicated NFSe screen.'
            : undefined;

    return {
        current,
        currentDetail,
        currentValue,
        payments,
        totalRecebido,
        desconto,
        acrescimo,
        total,
        sold,
        gerouNota,
        gerouNotaFiscal,
        sale,
        setSold,
        setGerouNota,
        setGerouNotaFiscal,
        handleValueChange,
        handleDescontoBlur,
        handleAcrescimoBlur,
        choosePayment,
        finalizarVenda,
        close,
        goBack,
        showFiscal,
        showNonFiscal,
        fiscalTooltip,
        tiposPagamento,
    };
}