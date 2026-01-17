import { axiosClient } from '../constants';

export async function validateProductStock(
    product_id: number,
    qtd = 1,
    usuario_id?: number,
    filial_id?: number | string | null,
    empresa_id?: number,
) {
    try {
        const { data } = await axiosClient.get('/api/produtos/valida-estoque', {
            params: { product_id, qtd, usuario_id, filial_id, empresa_id },
        });
        return data;
    } catch (error: any) {
        if (error?.response?.data) {
            const data = error.response.data;
            const msg =
                (typeof data === 'string' && data) ||
                data?.message ||
                JSON.stringify(data);
            throw new Error(msg);
        }
        throw error;
    }
}
