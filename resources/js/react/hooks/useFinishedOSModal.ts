import { useDispatch } from 'react-redux';
import Swal from 'sweetalert2';
import { axiosClient, Modal } from '../constants';
import { makeOsTable } from '../helpers/os';
import {
    finishPage,
    setItems,
    setOrdemServicoHasProduto,
    setOrdemServicoHasServico,
    setOrdemServicoId,
    setPaymentUrl,
} from '../store/slices/pdvSlice';
import { FinishedOS, Item } from '../types';

export default function useFinishedOSModal(empresa_id: number) {
    const dispatch = useDispatch();
    return async function openFinishedOSModal() {
        try {
            const { data } = await axiosClient.get(
                `/api/ordemServico/listar-finalizadas?empresa_id=${empresa_id}`,
                { withCredentials: true },
            );

            const ordens = Array.isArray(data) ? data : [];

            const osTratada: FinishedOS[] = ordens.map((os: any) => ({
                id: os.id,
                codigo_sequencial: os.codigo_sequencial,
                cliente: os.cliente?.razao_social || '-',
                valor_total: Number(os.valor),
                data_finalizada: os.data_entrega
                    ? os.data_entrega.split(' ')[0]
                    : os.updated_at
                      ? os.updated_at.split('T')[0]
                      : '-',
                funcionario: os.funcionario?.nome || '-',
                has_produto: (os.itens || []).length > 0,
                has_servico: (os.servicos || []).length > 0,
            }));

            const tableHtml = makeOsTable(osTratada);

            await Modal.fire({
                title: 'Ordem de Serviços Finalizadas',
                html: tableHtml,
                showConfirmButton: false,
                showCloseButton: true,
                width: '60vw',
                customClass: {
                    popup: 'larger-modal',
                },
                didOpen: () => {
                    document
                        .querySelectorAll<HTMLButtonElement>('.btn-pagar-os')
                        .forEach((btn) => {
                            btn.disabled = false;
                            btn.style.cursor = 'pointer';
                            btn.addEventListener('click', () => {
                                const osData = btn.getAttribute('data-os');
                                if (!osData) return;
                                const os = JSON.parse(osData);
                                const item = {
                                    value: os.valor_total,
                                    label: `OS ${os.id}`,
                                    id: os.id,
                                    nome: `OS ${os.id}`,
                                    qtd: 1,
                                    vl_total: os.valor_total,
                                    empresa_id: empresa_id,
                                    categoria_id: 0,
                                    sub_categoria_id: null,
                                    padrao_id: null,
                                    marca_id: null,
                                    fabricante: null,
                                    variacao_modelo_id: null,
                                    codigo_barras: '',
                                    codigo_barras2: '',
                                    codigo_barras3: '',
                                    referencia: '',
                                    ncm: '',
                                    unidade: 'UN',
                                    imagem: '',
                                    perc_icms: '0',
                                    perc_pis: '0',
                                    perc_cofins: '0',
                                    perc_ipi: '0',
                                    cest: null,
                                    origem: 0,
                                    cst_csosn: '',
                                    cst_pis: '',
                                    cst_cofins: '',
                                    cst_ipi: '',
                                    perc_red_bc: null,
                                    pST: null,
                                    valor_unitario: String(os.valor_total),
                                    valor_compra: '0',
                                    percentual_lucro: '0',
                                    cfop_estadual: '',
                                    cfop_outro_estado: '',
                                    cfop_entrada_estadual: '',
                                    cfop_entrada_outro_estado: '',
                                } as Item;
                                dispatch(setItems([item]));
                                dispatch(
                                    setPaymentUrl('api/ordemServico/pagar'),
                                );
                                dispatch(setOrdemServicoId(os.id));
                                dispatch(
                                    setOrdemServicoHasProduto(os.has_produto),
                                );
                                dispatch(
                                    setOrdemServicoHasServico(os.has_servico),
                                );
                                dispatch(finishPage(true));
                                Modal.close();
                            });
                        });
                },
            });
        } catch (e) {
            Swal.fire(
                'Erro',
                'Não foi possível buscar as OS finalizadas!',
                'error',
            );
        }
    };
}