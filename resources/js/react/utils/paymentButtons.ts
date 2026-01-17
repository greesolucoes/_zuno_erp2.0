import { MultiPayment } from '../types';

export function updatePaymentButtonsDisabledState(
    _payments: MultiPayment[],
    total: number,
    totalRecebido: number
) {
    for (const button of document.querySelectorAll('.forma_pagamento') as unknown as HTMLButtonElement[]) {
        const classList = [...button.classList];
        button.disabled = total <= totalRecebido && !classList.includes('active');
    }
}
