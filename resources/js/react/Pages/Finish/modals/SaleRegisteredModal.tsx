import React from 'react';
import { Modal } from '../../../constants';

export default async function openSaleRegisteredModal(showObs: boolean): Promise<void> {
    await Modal.fire({
        icon: 'success',
        title: 'Venda Registrada!',
        html: showObs ? (
            <p className="mt-2">Obs: o cupom fiscal é gerado apenas para produtos</p>
        ) : undefined,
        showConfirmButton: false,
        timer: 2000,
    });
}
