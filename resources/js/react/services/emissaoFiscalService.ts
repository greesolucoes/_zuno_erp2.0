import { axiosClient, Modal } from '../constants';
import { appendLoadingElement, removeLoadingElement } from '../helpers';
import Swal from 'sweetalert2';
import { isAxiosError } from 'axios';

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: toast => {
        (toast as HTMLElement).onmouseenter = Swal.stopTimer;
        (toast as HTMLElement).onmouseleave = Swal.resumeTimer;
    },
});

export interface EmissaoFiscalOptions {
    empresa_id?: number;
    ordem_servico_id?: number;
    ordem_servico_has_produto?: boolean;
    ordem_servico_has_servico?: boolean;
    /** redirecionar para /frontbox em caso de erro?  (default: true) */
    redirectOnError?: boolean;
}

export async function emitirFiscal(
    saleId: string,
    {
        empresa_id,
        ordem_servico_id,
        ordem_servico_has_produto,
        ordem_servico_has_servico,
        redirectOnError = true,
    }: EmissaoFiscalOptions = {},
) {
    const $body = document.body;
    $body.classList.add('loading');
    appendLoadingElement();
    try {
        /* ---------------------------------------------------------- */
        /*  FLUXO ORDEM DE SERVIÇO                                    */
        /* ---------------------------------------------------------- */
        if (ordem_servico_id) {
            let urlPrint: string | null = null;

            if (ordem_servico_has_produto) {
                const {
                    data,
                } = await Promise.all([
                    axiosClient.post(`api/ordemServico/emit-nfce-produto/${saleId}`),
                    Toast.fire({
                        icon: 'info',
                        text: 'Emitindo Cupom Fiscal...',
                        customClass: { popup: 'z-index' },
                    }),
                ]).then(([response]) => response);

                await Modal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    html: 'NFC-e emitida com sucesso.',
                    showConfirmButton: false,
                    timer: 2000,
                });

                urlPrint = data?.url_print ?? null;
            }

            if (ordem_servico_has_produto) {
                window.open(urlPrint ?? `/nfce/imprimir/${saleId}`, '_blank');
            }
            if (ordem_servico_has_servico) {
                try {
                    const { data: os } = await axiosClient.get(`api/ordemServico/get-os/${saleId}`);
                    const c = os?.cliente || {};
                    const hasAddress =
                        c.rua && c.numero && c.bairro && c.cidade_id && c.cep;
                    if (!hasAddress) {
                        await Swal.fire({
                            icon: 'warning',
                            title: 'Cadastro Incompleto',
                            text: 'Cliente sem endereço cadastrado. Atualize o cadastro para emitir a NFS-e.',
                        });
                        return false;
                    }
                    window.open(`/ordem-servico/gerar-nfse/${saleId}`, '_blank');
                } catch (e) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Não foi possível verificar o endereço do cliente.',
                    });
                    return false;
                }
            }

            return true; // encerra aqui
        }

        /* ---------------------------------------------------------- */
        /*  FLUXO PDV “NORMAL”                                        */
        /* ---------------------------------------------------------- */
        if (!empresa_id) {
            throw new Error('empresa_id é obrigatório para emitir NFC-e');
        }

        const [{ data: payload }] = await Promise.all([
            axiosClient.post('api/nfce/transmitir', { id: saleId, empresa_id }),
            Toast.fire({
                icon: 'info',
                text: 'Emitindo Cupom!',
                customClass: { popup: 'z-index' },
            }),
        ]);

        await Modal.fire({
            icon: 'success',
            title: 'Sucesso',
            html: `NFC-e emitida ${typeof payload === 'string' ? payload : ''}`,
            showConfirmButton: false,
            timer: 2000,
        });

        window.open(`/nfce/imprimir/${saleId}`, '_blank');
        return true;
    } catch (e: unknown) {
        /* ---------------------------------------------------------- */
        /*  TRATAMENTO DE ERRO                                        */
        /* ---------------------------------------------------------- */
        if (isAxiosError(e)) {
            if (e.response) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Problema ao emitir NFC-e',
                    text: e.response.data.message,
                });
            } else {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Problema ao emitir NFC-e',
                    text: 'Nenhuma resposta recebida do servidor, tente novamente mais tarde',
                });
            }
        } else {
            await Swal.fire({
                icon: 'warning',
                title: 'Problema ao emitir NFC-e',
                text: 'Erro ao emitir NFC-e, tente novamente mais tarde',
            });
        }

        /* ----------------------------------------------------------
         *  Só redireciona se:
         *    1. NÃO for Ordem de Serviço  (ordem_servico_id indefinido)
         *    2. E se redirectOnError estiver habilitado
         * -------------------------------------------------------- */
        if (!ordem_servico_id && redirectOnError) {
            window.open('/frenteCaixa', '_self');
        }

        return false;
    } finally {
        $body.classList.remove('loading');
        removeLoadingElement();
    }
}
