import React, { FC, MouseEventHandler, useEffect, useRef } from 'react';
import { TiposPagamento } from '../../types';

const PaymentsMethods: FC<{
    tiposPagamento: TiposPagamento;
    currentValue: string;
    choosePayment: (
        key: string,
        value: string,
    ) => MouseEventHandler<HTMLButtonElement>;
}> = ({ currentValue, tiposPagamento, choosePayment }) => {
    const buttonsRef = useRef<(HTMLButtonElement | null)[]>([]);

    useEffect(() => {
        console.log(
            currentValue,
            !currentValue,
            typeof currentValue,
            currentValue === '' || currentValue === '0' || !currentValue,
        );
        buttonsRef.current.forEach((button) => {
            if (button) {
                button.disabled =
                    currentValue === '' ||
                    currentValue === '0' ||
                    !currentValue;
            }
        });
    }, [currentValue]);
    return (
        <>
            {Object.entries(tiposPagamento).map(([key, value], index) => (
                <button
                    key={index}
                    className="btn forma_pagamento border-0 text-light"
                    onClick={choosePayment(
                        key,
                        value.includes('PIX') ? 'PIX' : value,
                    )}
                    ref={(el) => {
                        buttonsRef.current[index] = el;
                    }}
                >
                    {value.includes('PIX') ? 'PIX' : value}
                </button>
            ))}
        </>
    );
};

export default PaymentsMethods;
