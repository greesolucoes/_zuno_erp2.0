import { axiosClient, Modal } from '../constants';
import { reload } from '../helpers';

export default function useSuspendSale(payload: any) {
    return async function suspendSale() {
        const { isConfirmed } = await Modal.fire({
            icon: 'question',
            title: 'Você esta certo?',
            text: 'Deseja suspender esta venda?',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Suspender',
            focusConfirm: false,
            customClass: {
                popup: 'larger-modal',
                actions: 'gird-col-2 mt-5',
            },
            preConfirm: () => true,
        });
        if (isConfirmed) {
            try {
                await axiosClient.post('api/frenteCaixa/suspender', payload, {
                    withCredentials: true,
                });
                Modal.fire({
                    title: 'Venda suspensa',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                });
                reload();
            } catch (error) {
                Modal.fire({
                    title: 'Não foi possivel suspender a venda',
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000,
                });
                console.error(error);
            }
        }
    };
}
