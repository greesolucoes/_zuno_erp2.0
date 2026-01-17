import React from 'react';
import { Modal } from '../../../constants';
import { formatToCurrency } from '../../../helpers';

export default async function openChangeConfirmation(value: number): Promise<boolean> {
    const { isConfirmed } = await Modal.fire({
        html: (
            <section className="section_modal">
                <h2>Confirmar troco de {formatToCurrency(value)}</h2>
            </section>
        ),
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não',
        customClass: {
            popup: 'larger-modal',
            actions: 'gird-col-1 mt-5',
            title: 'mt-5',
        },
    });
    return isConfirmed;
}