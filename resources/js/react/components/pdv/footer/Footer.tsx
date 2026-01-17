import { AxiosError } from 'axios';
import { FC } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import Swal from 'sweetalert2';

import { axiosClient, Modal } from '../../../constants';
import {
    appendLoadingElement,
    removeLoadingElement
} from '../../../helpers';
import { formatToCurrency } from '../../../helpers/currency';
import { IStore } from '../../../store';
import {
    calculateGlobalTotal,
    finishPage,
    getPayloadToSuspendSale,
    openClose,
    resetState,
} from '../../../store/slices/pdvSlice';
import '../../../style/Footer.css';

/* ----------------------------------------------------------------
   TIPAGEM DOS PROPS
-----------------------------------------------------------------*/
interface FooterProps {
    empresa_id   : number;
    usuario_id   : number;
    id_abertura  : number;
    operador_nome: string;
    terminal_id? : string;   // default “001”
    pre_venda?   : boolean;
}

const Footer: FC<FooterProps> = ({
                                     empresa_id,
                                     usuario_id,
                                     id_abertura,
                                     operador_nome,
                                     terminal_id = '001',
                                     pre_venda,
                                 }) => {

    const dispatch = useDispatch();

    /* --------------------------- seletores --------------------------- */
    const total   = useSelector(calculateGlobalTotal);
    const seller  = useSelector((state: IStore) => state.store.vendedor);
    const client  = useSelector((state: IStore) => state.store.cliente);
    const items   = useSelector((state: IStore) => state.store.items);

    const clientCredit = client ? Number(client.valor_credito || 0) : 0;

    /* --------------------- payload para pré-venda -------------------- */
    const salePayload = useSelector(
        getPayloadToSuspendSale(empresa_id, usuario_id, total),
    );

    /* ============================ ACTIONS ============================ */
    const handleCloseShiftAndLogout = async () => {
        const result = await Swal.fire({
            title: 'Tem certeza?',
            text : 'Deseja realmente sair do PDV?',
            icon : 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, sair',
            cancelButtonText : 'Cancelar',
        });
        if (!result.isConfirmed) return;

        try {
            const { data: resumo } = await axiosClient.get(
                '/api/pdv/get-vendas-caixa',
                { params: { caixa_id: id_abertura } },
            );

            const saldo_final =
                resumo.totalDeVendas -
                resumo.totalSangrias +
                resumo.totalSuprimentos;

            await axiosClient.post('/api/pdv/fechar-turno-e-logout', {
                caixa_diario_id: id_abertura,
                saldo_final,
                observacao: '',
            });

            dispatch(resetState());
            window.location.reload();
        } catch (e: any) {
            const msg = e?.response?.data?.message || e?.message || '';
            Swal.fire('Erro', 'Erro ao fechar turno e sair: ' + msg, 'error');
        }
    };

    const openFinishSaleModal = async () => {
        /* ----------- PRÉ-VENDA ----------- */
        if (pre_venda) {
            try {
                await axiosClient.post('api/pre-venda/store', {
                    ...salePayload,
                    usuario_id,
                });

                await Modal.fire({
                    icon: 'success',
                    title: 'Pré-Venda Realizada!',
                    showConfirmButton: false,
                    timer: 2000,
                });

                dispatch(resetState());
                document.body.classList.add('loading');
                appendLoadingElement();

                setTimeout(() => {
                    dispatch(openClose(true));
                    document.body.classList.remove('loading');
                    removeLoadingElement();
                }, 1000);
            } catch (e: unknown | AxiosError) {
                await Swal.fire({
                    icon : 'error',
                    title: 'Erro!',
                    text : `Não foi possível registrar esta venda! ${(e as any).response.data}`,
                });
            }
            return;
        }
        /* ----------- VENDA NORMAL ----------- */
        dispatch(finishPage());
    };

    /* ============================ RENDER ============================ */
    return (
        <footer>
            {/* BLOCO ESQUERDO ------------------------------------------------ */}
            <div className="footer__container">
                <div className="footer__infos">

                    {/* <span className="footer__info">
                        <b>Terminal PDV:</b><em>{terminal_id}</em>
                    </span> */}



                    <span className="footer__info">
                        <b>Vendedor:</b><em>{seller?.nome || '---'}</em>
                    </span>

                    <span className="footer__info">
                        <b>Cliente:</b>
                        <em>
                            {client
                                ? client.nome_fantasia    // 1ª opção
                                || client.razao_social // 2ª
                                || client.label        // 4ª (caso venha do Autocomplete)
                                || '---'
                                : '---'}
                        </em>
                        {client && clientCredit > 0 ? (
                            <span className="footer__credit">
                                Crédito: {formatToCurrency(clientCredit)}
                            </span>
                        ) : null}
                    </span>


                </div>
            </div>

            {/* BLOCO DIREITO ------------------------------------------------- */}
            <div className="footer__containte--actions">
                <div id="action_container">

                    <button
                        id="finalizar"
                        className="action__button"
                        onClick={openFinishSaleModal}
                        disabled={!items.length}
                    >
                        <img
                            src="/assets/images/pdv/shopping.png"
                            alt=""
                            height={48}
                        />
                        {pre_venda ? 'Finalizar Pré-Venda' : 'Finalizar Venda'}
                    </button>

                    {/* BOTÃO OPCIONAL DE LOGOUT
                    <button
                        className="action__button"
                        onClick={handleCloseShiftAndLogout}
                    >
                        <img
                            src="/assets/images/pdv/logout.svg"
                            alt="Sair do PDV"
                            height={48}
                        />
                        Sair do PDV
                    </button> */}
                </div>
            </div>
        </footer>
    );
};

export default Footer;
