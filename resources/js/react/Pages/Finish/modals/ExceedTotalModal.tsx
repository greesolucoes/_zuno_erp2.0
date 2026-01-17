import React from 'react';
import { Modal } from '../../../constants';

const ExceedTotalContent = () => (
    <section className="section_modal">
        <h2>Total recebido excede valor total</h2>
    </section>
);

export default async function openExceedTotalModal() {
    await Modal.fire({
        icon: 'warning',
        html: <ExceedTotalContent />,
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
