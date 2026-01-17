import React from 'react';
import { Modal } from '../../../constants';
import { formatToCurrency } from '../../../helpers';

const MinValueContent: React.FC<{ value: number }> = ({ value }) => (
    <section className="section_modal">
        <h2>Valor mínimo para esta forma de pagamento é {formatToCurrency(value)}</h2>
    </section>
);

export default async function openMinValueModal(value: number) {
    await Modal.fire({
        icon: 'warning',
        html: <MinValueContent value={value} />,
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
