import React from 'react';
import { Modal } from '../../../constants';

export interface AdjustmentConfirmationParams {
    desconto?: number;
    acrescimo?: number;
}

export default async function openPaymentAdjustmentConfirmation({ desconto, acrescimo }: AdjustmentConfirmationParams): Promise<boolean> {
    const parts: string[] = [];
    if (desconto && desconto > 0) parts.push(`${desconto}% de desconto`);
    if (acrescimo && acrescimo > 0) parts.push(`${acrescimo}% de acréscimo`);
    const text = parts.join(' e ');
    const { isConfirmed } = await Modal.fire({
        title: 'Deseja aplicar os ajustes detectados na forma de pagamento?',
        html: (
            <section className="section_modal">
                <p>
              A forma de pagamento selecionada inclui <strong>{text}</strong>. Confirma a aplicação desse(s) ajuste(s) ao valor final?
                </p>

            </section>
        ),
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Sim, aplicar ajustes',
        cancelButtonText: 'Cancelar e manter valores originais',
        customClass: {
            popup: 'larger-modal',
            actions: 'gird-col-1 mt-5',
            title: 'mt-5',
        },
    });
    return isConfirmed;
}
