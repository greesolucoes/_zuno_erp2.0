import openCreditCardModal from '../modals/CreditCardModal';
import openCrediarioModal from '../modals/CrediarioModal';
import openCpfNotaModal from '../modals/CpfNotaModal';
import { FormaPagamentoDetalhe } from '../../../types';

export default class FinishModal {
    static creditCard(
        detail: FormaPagamentoDetalhe | undefined,
        base: number,
    ) {
        return openCreditCardModal(detail, base);
    }

    static crediario(
        detail: FormaPagamentoDetalhe | undefined,
        empresa_id: number,
        base: number,
    ) {
        return openCrediarioModal(detail, empresa_id, base);
    }

    static cpfNota() {
        return openCpfNotaModal();
    }
}
