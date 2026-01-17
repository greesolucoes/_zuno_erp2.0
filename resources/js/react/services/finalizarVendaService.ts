import { AxiosError } from 'axios';
import Swal from 'sweetalert2';
import { axiosClient } from '../constants';
import FinishModal from '../Pages/Finish/modals/FinishModal';
import { openSaleSuccessModal } from '../Pages/Finish/modals';

export interface FinalizarVendaParams {
    url: string;
    salePayload: Record<string, string | number | string[]>;
    usuario_id: number;
    ordem_servico_id?: number;
    caixa_id?: number;
    sale: { [key: string]: string | number; id: string | number } | undefined;
    setSale: (sale: any) => void;
    ordem_servico_has_produto?: boolean;
    cpf_na_nota?: boolean;
    onSuccess: (saleId: string) => void | Promise<void>;
    onError?: (e: unknown) => void | Promise<void>;
}

export async function finalizarVenda({
                                         url,
                                         salePayload,
                                         usuario_id,
                                         ordem_servico_id,
                                         caixa_id,
                                         sale,
                                         setSale,
                                         ordem_servico_has_produto,
                                         cpf_na_nota = false,
                                         onSuccess,
                                         onError,
                                     }: FinalizarVendaParams) {
    try {
        let othersFields: Record<string, string> = {};
        let currentSaleId = sale?.id;
        if (cpf_na_nota) {
            const result = await FinishModal.cpfNota();
            if (result) {
                othersFields = {
                    cliente_cpf_cnpj: result.cpf_cnpj,
                    cliente_nome: result.nome,
                };
            }
        }
        if (!sale) {
            const { data } = await axiosClient.post(url, {
                ...salePayload,
                ...othersFields,
                usuario_id,
                ...(ordem_servico_id ? { ordem_servico_id } : {}),
                ...(caixa_id ? { caixa_id } : {}),
            });
            setSale(data);
            currentSaleId = data.id;
        }
        await openSaleSuccessModal(
            Boolean(ordem_servico_id && !ordem_servico_has_produto),
        );
        await onSuccess(String(currentSaleId));
    } catch (e: unknown | AxiosError) {
        const data = (e as any)?.response?.data;
        const msg =
            (typeof data === 'string' && data) ||
            data?.message ||
            (e as any)?.message ||
            'Não foi possível registrar esta venda.';
        await Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: msg,
        });
        console.error(e);
        if (onError) onError(e);
    }
}
