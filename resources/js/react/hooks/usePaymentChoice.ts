import { useCallback } from 'react';
import {
    handleDiscount,
    setAcrescimo,
    setCardDetails,
    setClient,
    addDescontoFormaPagamento,
    removeDescontoFormaPagamento,
    addAcrescimoFormaPagamento,
    removeAcrescimoFormaPagamento,
} from '../store/slices/pdvSlice';
import { convertBrazilianCurrencyToFloat } from '../helpers';
import FinishModal from '../Pages/Finish/modals/FinishModal';
import {
    openExceedTotalModal,
    openMinValueModal,
} from '../Pages/Finish/modals';
import { openPaymentAdjustmentConfirmation } from '../Pages/Finish/modals';
import { MultiPayment, FormaPagamentoDetalhe } from '../types';
import type { AppDispatch } from '../store';
import { updatePaymentButtonsDisabledState } from '../utils/paymentButtons';

export interface UsePaymentChoiceArgs {
    pagamentosAtuais: MultiPayment[];
    total: number;
    detalhesMap: Map<string, FormaPagamentoDetalhe>;
    currentValue: string;
    empresa_id: number;
    dispatch: AppDispatch;
    setCurrent: (p: MultiPayment | undefined) => void;
    setCurrentDetail: (d: FormaPagamentoDetalhe | undefined) => void;
    setCurrentValue: (v: string) => void;
    setPayments: (cb: (prev: MultiPayment[]) => MultiPayment[]) => void;
    totalRecebido: number;
}

export function usePaymentChoice({
                                     pagamentosAtuais,
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
                                 }: UsePaymentChoiceArgs) {
    const choosePayment = useCallback(
        (key: string, value: string) => async (e: React.MouseEvent<HTMLButtonElement>) => {
            const target = e.currentTarget;
            const classList = [...target.classList];
            const isRemove = classList.includes('active');
            const valorInput = document.getElementById('valor_recebido') as HTMLInputElement;
            const enteredValue = convertBrazilianCurrencyToFloat(currentValue);
            const remainingTotalRaw = total - totalRecebido;
            const remainingTotal = Number(Math.max(remainingTotalRaw, 0).toFixed(2));
            const baseCandidate = enteredValue > 0 ? enteredValue : remainingTotal;
            const baseAmount =
                remainingTotal > 0
                    ? Math.min(baseCandidate, remainingTotal)
                    : baseCandidate;
            const baseAmountToCharge = Number(Math.max(baseAmount, 0).toFixed(2));
            let isOk = true;
            const detCurrent = detalhesMap.get(key);

            console.log('[usePaymentChoice] escolha iniciada', {
                key,
                label: value,
                isRemove,
                enteredValue,
                remainingTotalRaw,
                remainingTotal,
                baseCandidate,
                baseAmount: baseAmountToCharge,
                descontoAutomatico: detCurrent?.desconto_automatico ?? 0,
                acrescimoAutomatico: detCurrent?.acrescimo_automatico ?? 0,
                acrescimoParcelamento: detCurrent?.acrescimo_parcelamento ?? 0,
                parcelasDisponiveis: detCurrent?.parcelas_disponiveis,
                total,
                totalRecebido,
            });
            let parcelas = 1;
            let extra = 0;
            let valorFinal = 0;
            if (!isRemove && key == '03') {
                const result = await FinishModal.creditCard(
                    detCurrent,
                    baseAmountToCharge,
                );
                console.log('[usePaymentChoice] retorno cartão', result);
                isOk = result.isConfirmed;
                parcelas = result.vezes;
                extra = result.extra;
                valorFinal = result.valorFinal;
                if (result.isConfirmed) {
                    dispatch(setCardDetails({ repeat: Number(result.vezes) }));
                }
            }
            if (!isRemove && key == '06') {
                const result = await FinishModal.crediario(
                    detCurrent,
                    empresa_id,
                    baseAmountToCharge,
                );
                console.log('[usePaymentChoice] retorno crediário', result);
                if (result) {
                    parcelas = result.parcelas;
                    const valorParcela = baseAmountToCharge / parcelas;
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
                        const novoTotal = currentPays.reduce((acc, { value }) => acc + value, 0);
                        setCurrentValue('');
                        updatePaymentButtonsDisabledState(currentPays, total, novoTotal);
                        return currentPays;
                    });
                    target.classList.add('active');
                }
                isOk = false;
            }
            const baseInput = baseAmountToCharge;
            if (isOk && key !== '01') {
                const predictedTotal = total + (extra || 0);
                const recebido = totalRecebido + (valorFinal || baseInput);
                if (recebido > predictedTotal) {
                    isOk = false;
                    await openExceedTotalModal();
                    setCurrentValue('');
                    valorInput.value = '';
                    valorInput.focus();
                }
            }
            if (!isRemove && isOk && detCurrent && detCurrent.valor_minimo > 0) {
                if (baseAmountToCharge < detCurrent.valor_minimo) {
                    isOk = false;
                    await openMinValueModal(detCurrent.valor_minimo);
                }
            }
            if (isOk) {
                let applyAdjustments = true;
                if (!isRemove) {
                    const pctDesconto = detCurrent?.desconto_automatico ?? 0;
                    let pctAcrescimo = detCurrent?.acrescimo_automatico ?? 0;
                    if (!pctAcrescimo && extra && baseInput) {
                        pctAcrescimo = (extra / baseInput) * 100;
                    }
                    console.log('[usePaymentChoice] ajustes automáticos', {
                        key,
                        descontoPct: pctDesconto,
                        acrescimoPct: pctAcrescimo,
                        extraInformado: extra,
                        base: baseInput,
                    });
                    if (pctDesconto > 0 || pctAcrescimo > 0) {
                        applyAdjustments = await openPaymentAdjustmentConfirmation({
                            desconto: pctDesconto > 0 ? Number(pctDesconto) : undefined,
                            acrescimo: pctAcrescimo > 0 ? Number(pctAcrescimo) : undefined,
                        });
                        console.log('[usePaymentChoice] confirmação ajustes', {
                            key,
                            aplicar: applyAdjustments,
                        });
                    }
                }

                target.classList.toggle('active');
                if (isRemove) {
                    setPayments((prev) => {
                        const currentPays = prev.filter(({ payment_method }) => payment_method !== key);
                        setCurrent(undefined);
                        setCurrentDetail(undefined);
                        setCurrentValue('');
                        valorInput.value = '';
                        valorInput.focus();
                        const novoTotal = currentPays.reduce((acc, { value }) => acc + value, 0);
                        updatePaymentButtonsDisabledState(currentPays, total, novoTotal);
                        dispatch(handleDiscount(0));
                        dispatch(setAcrescimo(0));
                        dispatch(removeDescontoFormaPagamento(key));
                        dispatch(removeAcrescimoFormaPagamento(key));
                        return currentPays;
                    });
                } else {
                    valorInput.value = '';
                    const now = new Date();
                    let paymentAmount = baseInput;
                    let extraValue = 0;
                    let discountValue = 0;
                    if (applyAdjustments) {
                        extraValue = extra || 0;
                        if (!extraValue && detCurrent?.acrescimo_automatico) {
                            extraValue =
                                (baseInput * detCurrent.acrescimo_automatico) / 100;
                        }
                        if (detCurrent?.desconto_automatico) {
                            discountValue =
                                (baseInput * detCurrent.desconto_automatico) / 100;
                        }
                    }
                    if (detCurrent && (extraValue || discountValue)) {
                        paymentAmount = baseInput + extraValue - discountValue;
                    }
                    const currentPayment: MultiPayment = {
                        description: '',
                        expiration_date: now.toISOString().split('T')[0],
                        value: paymentAmount,
                        payment_label: value,
                        payment_method: key,
                        extra: extraValue || undefined,
                    };
                    if (applyAdjustments) {
                        setCurrent(currentPayment);
                    }
                    setCurrentDetail(detalhesMap.get(key));
                    const det = detalhesMap.get(key);
                    if (det && applyAdjustments) {
                        if (discountValue) {
                            dispatch(handleDiscount(discountValue));
                            dispatch(addDescontoFormaPagamento(key));
                        }
                        const computedExtra = extraValue;
                        if (computedExtra) {
                            dispatch(setAcrescimo(computedExtra));
                            dispatch(addAcrescimoFormaPagamento(key));
                        }
                    }
                    console.log('[usePaymentChoice] pagamento aplicado', {
                        key,
                        valorBase: baseInput,
                        paymentAmount,
                        extraValue,
                        discountValue,
                        applyAdjustments,
                    });
                    setPayments((prev) => {
                        const currentPays = [...prev, currentPayment];
                        const novoTotal = currentPays.reduce((acc, { value }) => acc + value, 0);
                        setCurrentValue('');
                        updatePaymentButtonsDisabledState(currentPays, total, novoTotal);
                        return currentPays;
                    });
                }
            }
        },
        [
            pagamentosAtuais,
            currentValue,
            total,
            totalRecebido,
            detalhesMap,
            empresa_id,
            dispatch,
            setCurrent,
            setCurrentDetail,
            setCurrentValue,
            setPayments,
        ]
    );

    return choosePayment;
}