import React from 'react';
import { Modal } from '../../../constants';

const SuccessContent: React.FC = () => (
    <p className="mt-2">Obs: o cupom fiscal é gerado apenas para produtos</p>
);

export default async function openSaleSuccessModal(showWarning: boolean) {
    await Modal.fire({
        icon: 'success',
        title: 'Venda Registrada!',
        html: showWarning ? <SuccessContent /> : undefined,
        showConfirmButton: false,
        timer: 2000,
    });
}
