import { axiosClient, Modal } from '../../constants';
import SalesSuspended from './SalesSuspended';

export default async function openSalesSuspendedModal(empresa_id: number) {
    const { data: htmlContent } = await axiosClient.get(
        `api/frenteCaixa/venda-suspensas?empresa_id=${empresa_id}`,
        { withCredentials: true },
    );
    await Modal.fire({
        title: 'Vendas Suspensas',
        html: <SalesSuspended {...{ htmlContent }} />,
        showConfirmButton: false,
        customClass: {
            popup: 'larger-modal d-flex flex-column',
            closeButton: 'ms-auto mb-4',
            actions: 'gird-col-2 mt-5',
            title: 'mb-3',
        },
    });
}
