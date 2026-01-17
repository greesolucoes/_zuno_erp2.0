import React from 'react';
import { Modal } from '../../../constants';
import { formatToCurrency } from '../../../helpers';

export default async function openMinimumValueWarning(minimum: number): Promise<void> {
    await Modal.fire({
        icon: 'warning',
        html: (
            <section className="section_modal">
                <h2>
                    Valor mínimo para esta forma de pagamento é {formatToCurrency(minimum)}
                </h2>
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
