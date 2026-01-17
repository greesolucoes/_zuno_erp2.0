import React, { FC, useEffect, useMemo } from 'react';
import SelectGroup from './SelectGroup';
import {
    formatToCurrency,
    ordenarPorValor,
    parseCurrencyToFloat,
} from '../../helpers';
import InputGroup from './InputGroup';
import { MultiPayment, TiposPagamento } from '../../types';
import InputCurrency from './InputCurrency';

const MultiPaymentForm: FC<{
    total: number;
    payments: MultiPayment[];
    tiposPagamento: TiposPagamento;
}> = ({ total, payments: multPayments, tiposPagamento }) => {
    const [payments, setPayments] =
        React.useState<MultiPayment[]>(multPayments);

    const dateToStr = (strDate: string) => {
        if (strDate === '') return '';
        const [year, month, day] = strDate.split('-');
        return `${day}/${month}/${year}`;
    };
    const add: React.FormEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();
        const payment_type = document.getElementById(
            'payment_type',
        ) as HTMLSelectElement;
        const payment_value = document.getElementById(
            'payment_value',
        ) as HTMLInputElement;
        const payment_expiration = document.getElementById(
            'payment_expiration',
        ) as HTMLInputElement;
        const payment_description = document.getElementById(
            'payment_description',
        ) as HTMLInputElement;

        if (
            !!payment_type &&
            !!payment_value &&
            !!payment_expiration &&
            !!payment_description
        ) {
            setPayments([
                ...payments,
                {
                    payment_method: payment_type.value,
                    payment_label:
                        payment_type.options[payment_type.selectedIndex]
                            .innerHTML,
                    value: parseCurrencyToFloat(payment_value.value),
                    description: payment_description.value,
                    expiration_date: payment_expiration.value,
                },
            ]);
            payment_type.value = '';
            payment_value.value = '';
            payment_expiration.value = new Date().toISOString().split('T')[0];
            payment_description.value = '';
        }
        return false;
    };
    const totalPayments = useMemo(
        () => payments.reduce((acc, current) => acc + current.value, 0),
        [payments],
    );
    useEffect(() => {
        const json_payments = document.getElementById(
            'json_payments',
        ) as HTMLInputElement;
        if (json_payments) {
            json_payments.value = JSON.stringify(payments);
        }
    }, [payments]);
    return (
        <div className={'container-fluid'}>
            <input type="hidden" id="json_payments" />
            <div className="row mb-4 border-bottom">
                <div className="col-12 d-flex justify-content-start align-items-center gap-2">
                    <p className={'left-text mb-0'}>Pagamento Múltiplo</p>
                    <b style={{ color: 'red' }}>{formatToCurrency(total)}</b>
                </div>
            </div>
            <form
                className="row d-flex justify-content-start align-items-end mb-5"
                onSubmit={add}
            >
                <div className="col-12 col-md-3">
                    <SelectGroup
                        id={'payment_type'}
                        label={'Tipo de Pagamento'}
                        required
                    >
                        <option value="" selected disabled hidden>
                            Selecione
                        </option>
                        {ordenarPorValor(Object.entries(tiposPagamento)).map(
                            ([key, value], index) => (
                                <option key={index} value={key}>
                                    {value}
                                </option>
                            ),
                        )}
                    </SelectGroup>
                </div>
                <div className="col-12 col-md-2">
                    <div className="form-group">
                        <InputCurrency
                            id={'payment_value'}
                            label={'Valor'}
                            className={'monetary'}
                            max={total}
                            min={0}
                            required
                        />
                    </div>
                </div>
                <div className="col-12 col-md-3">
                    <div className="form-group">
                        <InputGroup
                            id={'payment_expiration'}
                            label={'Vencimento'}
                            type={'date'}
                            defaultValue={
                                new Date().toISOString().split('T')[0]
                            }
                        />
                    </div>
                </div>
                <div className="col-12 col-md-3">
                    <div className="form-group">
                        <InputGroup
                            id={'payment_description'}
                            label={'Observação'}
                            type={'text'}
                        />
                    </div>
                </div>
                <div className="col-12 col-md-1">
                    <div className="h-100 w-100 d-flex justify-content-center align-items-end">
                        <button
                            className="btn button__add"
                            type="submit"
                            disabled={totalPayments === total}
                        >
                            +
                        </button>
                    </div>
                </div>
            </form>
            <div className="row">
                <div className="col-12">
                    <div className="table-responsive">
                        <table className="table" id={'multi-payment-table'}>
                            <thead>
                                <tr>
                                    <th scope="col">Tipo de Pagamento</th>
                                    <th scope="col">Vencimento</th>
                                    <th scope="col">Valor</th>
                                    <th scope="col">Observações</th>
                                    <th scope="col">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                {payments.map(
                                    (
                                        {
                                            payment_label,
                                            value,
                                            description,
                                            expiration_date,
                                        },
                                        index,
                                    ) => (
                                        <tr key={index}>
                                            <td>{payment_label}</td>
                                            <td>
                                                {dateToStr(expiration_date)}
                                            </td>
                                            <td>{formatToCurrency(value)}</td>
                                            <td>{description}</td>
                                            <td
                                                className={
                                                    'remove__item--payment'
                                                }
                                                onClick={() => {
                                                    setPayments(
                                                        payments.filter(
                                                            (item, i) =>
                                                                i !== index,
                                                        ),
                                                    );
                                                }}
                                            >
                                                X
                                            </td>
                                        </tr>
                                    ),
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div className="row mb-4">
                <div className="col-4 d-flex justify-content-start align-items-center gap-2 border-bottom">
                    <p className={'mr-4 left-text w-auto text-muted'}>
                        Soma pagamento:
                    </p>
                    <b className={'ml-3'}>{formatToCurrency(totalPayments)}</b>
                </div>
            </div>
            <div className="row">
                <div className="col-12 d-flex justify-content-start align-items-center border-bottom gap-2">
                    <p className={'mr-4 left-text  w-auto text-danger'}>
                        Diferença:
                    </p>
                    <b className={'text-danger'}>
                        {formatToCurrency(total - totalPayments)}
                    </b>
                </div>
            </div>
        </div>
    );
};

export default MultiPaymentForm;
