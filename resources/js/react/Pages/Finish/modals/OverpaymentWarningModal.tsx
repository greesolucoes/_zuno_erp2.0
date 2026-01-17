import React from 'react';
import { Modal } from '../../../constants';

export default async function openOverpaymentWarning(): Promise<void> {
    await Modal.fire({
        icon: 'warning',
        html: (
            <section className="section_modal">
                <h2>Total recebido excede valor total</h2>
            </section>
        ),
        focusConfirm: false,
        showConfirmButton: false,
        timer: 2500,
        customClass: {
            popup: 'larger-modal',
            actions: 'gird-col-1 mt-5',
            title: 'mt-5',
        },
    });
}
