import Swal from 'sweetalert2';

export default async function openSaleErrorModal(message?: string): Promise<void> {
    await Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: `Não foi possível registrar esta venda! ${message ?? ''}`,
    });
}
