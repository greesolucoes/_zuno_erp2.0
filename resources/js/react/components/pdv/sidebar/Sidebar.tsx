/**
 * Sidebar.tsx
 * ——————————————————————————————————————————
 * Substitui os <i class="fa-solid …"> por SVGs inline,
 * acrescenta modal de atalhos de teclado e corrige
 * todos os erros de tipagem (TS2741, TS2304, TS7006, TS7053).
 */

import React, { FC, useCallback, useEffect } from 'react';


import { useDispatch, useSelector } from 'react-redux';
import { axiosClient, Modal } from '../../../constants';
import { appendLoadingElement, removeLoadingElement } from '../../../helpers';
import useFinishedOSModal from '../../../hooks/useFinishedOSModal';
import useSuspendSale from '../../../hooks/useSuspendSale';
import { IStore } from '../../../store';
import {
    calculateGlobalTotal,
    finishPage,
    getPayloadToSuspendSale,
    openClose,
    resetState,
    setClient,
    setItems,
    setPreVendaId,
    setCodigoComanda,
    setVendedor,
    setTrocasOpen,
    setVendaSuspensaId,
} from '../../../store/slices/pdvSlice';
import openSalesSuspendedModal from '../../shared/SalesSuspendedModal';
import ActionPanel from '../wrapper/ActionPanel';

import { AxiosError } from 'axios';
import 'boxicons/css/boxicons.min.css';
import Swal from 'sweetalert2';
import useSangriaModal from '../../../hooks/useSangriaModal';
import useSuprimentoModal from '../../../hooks/useSuprimentoModal';
import '../../../style/sidebar.css';
import { TiposPagamento, TTeclasAtalhos, User, Vendedor } from '../../../types';
import GenericAsyncSelect from '../../shared/Autocomplete';
import SelectGroup from '../../shared/SelectGroup';

/* ------------------------------------------------------------------ */
/* ÍCONES SVG INLINE                                                   */
/* ------------------------------------------------------------------ */
const AnglesLeftIcon: FC<React.SVGProps<SVGSVGElement>> = (props) => (
    <svg viewBox="0 0 512 512" aria-hidden="true" {...props}>
        <path
            fill="currentColor"
            d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8
            12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8
            0-45.3s-32.8-12.5-45.3 0l-160 160zm352-160l-160 160c-12.5 12.5-12.5
            32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L301.3
            256 438.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0z"
        />
    </svg>
);

const AnglesRightIcon: FC<React.SVGProps<SVGSVGElement>> = (props) => (
    <svg viewBox="0 0 512 512" aria-hidden="true" {...props}>
        <path
            fill="currentColor"
            d="M470.6 278.6c12.5 12.5 12.5 32.8 0 45.3l-160 160c-12.5 12.5
            -32.8 12.5-45.3 0s-12.5-32.8 0-45.3L402.7 256 265.4 118.6c-12.5-12.5
            -12.5-32.8 0-45.3s32.8-12.5 45.3 0l160 160zm-352 160l160-160c12.5
            -12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5
            32.8 0 45.3L210.7 256 73.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8
            12.5 45.3 0z"
        />
    </svg>
);

/* ------------------------------------------------------------------ */
/* MAPA DE ATALHOS – Partial<Record<…>> evita TS2741                  */
/* ------------------------------------------------------------------ */
const MAPA_ATALHOS: Partial<Record<TTeclasAtalhos, string>> = {
    fluxo_diario:             'F9 – Fluxo diário',
    comanda:                  'F8 – Apontar comanda',
    devolucao:                'F2 – Devolução',
    trocas:                   'F3 – Trocas',
    venda_consignada:         'F4 – Venda consignada',
    pre_venda:                'F5 – Pré-venda',
    abrir_gaveta:             'F6 – Abrir gaveta',
    suspender_vendas:         'F7 – Suspender venda',
    vendas_suspensas:         'F8 – Vendas suspensas',
    resumo_de_vendas:         'F9 – Resumo de vendas',
    convenios_e_limite:       'F10 – Convênios / limite',
    scanner_produtos:         'F1 – Scanner produtos',
    cancelar_cupom:           'F11 – Cancelar cupom',
    consultar_cashback:       'F12 – Consultar cashback',
    recarga_celular:          'Ctrl + R – Recarga celular',
    reimpressao_de_nfc_e:     'Ctrl + P – Reimpressão NFC-e',
    suprimento:               'Ctrl + S – Suprimento',
    sangria:                  'Ctrl + G – Sangria',
    pagamentos_ordem_servico: 'Alt + P – Pagamentos O.S.',
    ordem_servico:            'Alt + O – Ordem de serviço',
    fechar_caixa:             'Ctrl + F – Fechar caixa',
};

/* ------------------------------------------------------------------ */
/* TIPOS E PROPS                                                      */
/* ------------------------------------------------------------------ */
type Props = {
    empresa_id: number;
    user: User;
    usuario_id: number;
    tiposPagamento: TiposPagamento;
    vendedor_obrigatorio: number;
    vendedores?: Vendedor[];
    id_abertura: number;
    pre_venda?: boolean;
    /** Lista vinda do backend; se ausente, mostra todos */
    teclas_atalhos?: TTeclasAtalhos[];
    openScannerModal: () => void;
};

/* ------------------------------------------------------------------ */
/* COMPONENTE                                                         */
/* ------------------------------------------------------------------ */
const Sidebar: FC<Props> = ({
                                empresa_id,
                                usuario_id,
                                user,
                                vendedor_obrigatorio = 0,
                                vendedores = [],
                                pre_venda,
                                id_abertura,
                                teclas_atalhos,
                                openScannerModal,
                            }) => {
    const dispatch = useDispatch();

    /* ---- REDUX STATE ------------------------------------------------ */
    const items  = useSelector((s: IStore) => s.store.items || []);
    const seller = useSelector((s: IStore) => s.store.vendedor);
    const isOpen = useSelector((s: IStore) => s.store.isOpen);
    const total  = useSelector(calculateGlobalTotal);

    const salePayload = useSelector(
        getPayloadToSuspendSale(empresa_id, usuario_id, total),
    );
    const suspendSale = useSuspendSale(salePayload);
    const openFinishedOSModal = useFinishedOSModal(empresa_id);
    const openSalesSuspended = () => openSalesSuspendedModal(empresa_id);

    const handleSangriaModal = useSangriaModal(
        id_abertura,
        usuario_id,
        empresa_id,
    );
    const handleSuprimentoModal = useSuprimentoModal(
        id_abertura,
        usuario_id,
        empresa_id,
    );
    const handleOrdemServicoModal = () => {
        window.location.href = '/ordem-servico/create';
    };
    const handleFecharCaixaModal = () => {
        window.location.href = '/caixa';
    };
    const lastSales = async () => {
        try {
            await axiosClient.post('api/nfe/pesquisar', { empresa_id });
        } catch (e) {
            console.error(e);
        }
    };

    /* ---- HANDLERS ---------------------------------------------------- */
    const toggleSidebar = () => dispatch(openClose(!isOpen));

    const handleCloseShiftAndLogout = async () => {
        const ask = await Swal.fire({
            title: 'Deseja sair do PDV?',
            text: "Ao sair, o turno atual será encerrado.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, sair',
            cancelButtonText: 'Cancelar',
        });
        if (!ask.isConfirmed) return;

        try {
            const { data: resumo } = await axiosClient.get(
                '/api/pdv/get-vendas-caixa',
                { params: { caixa_id: id_abertura } },
            );
            const saldo_final =
                resumo.totalDeVendas - resumo.totalSangrias + resumo.totalSuprimentos;

            const res = await axiosClient.post('/api/pdv/fechar-turno-e-logout', {
                caixa_diario_id: id_abertura,
                saldo_final,
                observacao: '',
            });

            if (res.data.redirect_home) {
                Swal.fire(
                    'Caixa fechado com sucesso!',
                    res.data.message,
                    'success',
                ).then((confirmation) => {
                    if (confirmation) {
                        window.location.href = '/';
                    }
                })

                return;
            }

            dispatch(resetState());
            window.location.reload();
        } catch (e: any) {
            const msg = e?.response?.data?.message || e?.message || '';
            Swal.fire('Erro', 'Erro ao fechar turno e sair: ' + msg, 'error');
        }
    };

    /** Modal de atalhos de teclado */
    const openShortcutsModal = () => {
        const fonte = teclas_atalhos && teclas_atalhos.length
            ? teclas_atalhos
            : (Object.keys(MAPA_ATALHOS) as TTeclasAtalhos[]);

        Modal.fire({
            title: 'Atalhos de Teclado',
            html: 
                <div className="atalhos-modal">
                    {fonte.map((key) => (
                        <div className='atalho-item'>
                            <div className="atalho-block">
                                {MAPA_ATALHOS[key]?.split('–')[0]}
                            </div>
                            {MAPA_ATALHOS[key]?.split('–')[1]}
                        </div>
                    ))}
                </div>
            ,
            width: 600,
            confirmButtonText: 'Fechar',
        });
    };

    const handleVendedor = async () => {
        /* … código inalterado … */
        let selectedVendedor: Vendedor | null = null;

        const { value: VendedorFinal } = await Modal.fire({
            html: (
                <section className="section_modal">
                    <h2>Selecionar Vendedor</h2>
                    <SelectGroup label="Vendedor:" id="vendedor" className="w-100">
                        <option disabled selected value="">
                            Selecione um dos vendedores
                        </option>
                        {vendedores.map(({ id, nome, cargo }) => (
                            <option key={id} value={id}>
                                {cargo}: {nome}
                            </option>
                        ))}
                    </SelectGroup>
                </section>
            ),
            confirmButtonText: 'Selecionar',
            focusConfirm: false,
            customClass: { popup: 'larger-modal' },
            preConfirm: () => {
                const vendedorID = Number(
                    (document.getElementById('vendedor') as HTMLSelectElement).value,
                );
                selectedVendedor =
                    vendedores.find(({ id }) => id === vendedorID) || null;

                if (!selectedVendedor) {
                    Modal.showValidationMessage('Você deve selecionar um Vendedor!');
                    return false;
                }
                return selectedVendedor;
            },
        });

        if (VendedorFinal) dispatch(setVendedor(VendedorFinal));
    };

    const handleClient = async () => {
        /* … código inalterado … */
        let selectedClient: any = null;

        const { value: clienteFinal } = await Modal.fire({
            html: (
                <section id="client_modal">
                    <h2>Buscar Cliente</h2>
                    <div className="w-100">
                        <label className="mb-2 d-flex w-100 text-start">Cliente:</label>
                        <GenericAsyncSelect
                            path="api/clientes/pesquisa"
                            params={{ empresa_id: String(empresa_id) }}
                            placeholder="Digite para buscar"
                            parseResponse={(c) => ({
                                value: c.id,
                                label: `${c.razao_social} - ${c.cpf_cnpj}`,
                                ...c,
                            })}
                            onChangeCallback={(c) => { selectedClient = c; }}
                            showSelectedOption
                        />
                    </div>
                </section>
            ),
            confirmButtonText: 'Selecionar',
            focusConfirm: false,
            customClass: { popup: 'larger-modal' },
            preConfirm: () => {
                if (!selectedClient) {
                    Modal.showValidationMessage('Você deve selecionar um cliente!');
                    return false;
                }
                return selectedClient;
            },
        });

        if (clienteFinal) dispatch(setClient(clienteFinal));
    };

    const openActionPanelModal = useCallback(() => {
        Modal.fire({

            html: (
                <ActionPanel
                    logo=""
                    itemSelecionado={null}
                    qtdRef={React.createRef<HTMLInputElement | null>()}


                    handleQuantityChange={() => {}}
                    handleAddItem={() => {}}
                    isEditing={false}
                    items={items}
                    teclas_atalhos={teclas_atalhos || []}
                    openFinishedOSModal={openFinishedOSModal}
                    openSalesSuspended={openSalesSuspended}
                    lastSales={lastSales}
                    handleSangriaModal={handleSangriaModal}
                    handleSuprimentoModal={handleSuprimentoModal}
                    handleFecharCaixaModal={handleFecharCaixaModal}
                    handleOrdemServicoModal={handleOrdemServicoModal}
                    handleScannerModal={openScannerModal}
                    suspenderVenda={suspendSale}
                    openTrocas={() => {
                        Modal.close();
                        dispatch(setTrocasOpen(true));
                    }}
                    handleFluxoDiarioModal={async () => {
                        try {
                            const { data } = await axiosClient.get('api/frenteCaixa/fluxo-diario', {
                                params: { empresa_id, usuario_id },
                            });

                            const abertura = data?.abertura;
                            const sangrias = data?.sangrias || [];
                            const suprimentos = data?.suprimentos || [];
                            const vendas = data?.vendas || [];

                            Modal.fire({
                                title: 'Fluxo Diário',
                                html: (
                                    <div style={{ textAlign: 'left' }}>
                                        <div><b>Abertura:</b> {abertura?.valor ?? '--'}</div>
                                        <hr />
                                        <div><b>Sangrias:</b></div>
                                        {sangrias.length ? sangrias.map((s: any, i: number) => (
                                            <div key={i}>- {s.valor} ({s.created_at})</div>
                                        )) : <div className="text-muted">Nenhuma</div>}
                                        <hr />
                                        <div><b>Suprimentos:</b></div>
                                        {suprimentos.length ? suprimentos.map((s: any, i: number) => (
                                            <div key={i}>- {s.valor} ({s.created_at})</div>
                                        )) : <div className="text-muted">Nenhum</div>}
                                        <hr />
                                        <div><b>Vendas:</b></div>
                                        {vendas.length ? (
                                            <table className="table table-striped table-sm mt-2">
                                                <thead><tr><th>Data</th><th>Valor</th><th>Pagamento</th></tr></thead>
                                                <tbody>
                                                {vendas.map((v: any, idx: number) => (
                                                    <tr key={idx}>
                                                        <td>{v.created_at}</td>
                                                        <td>{v.valor_total}</td>
                                                        <td>{v.tipo_pagamento}</td>
                                                    </tr>
                                                ))}
                                                </tbody>
                                            </table>
                                        ) : <div className="text-muted">Nenhuma venda</div>}
                                    </div>
                                ),
                                width: 800,
                                showCloseButton: true,
                                showConfirmButton: false,
                                customClass: { popup: 'larger-modal' },
                            });
                        } catch (e: any) {
                            const msg = e?.response?.data?.message || e?.message || 'Erro ao carregar fluxo diário';
                            Modal.fire({ icon: 'error', title: 'Erro', text: msg });
                        }
                    }}
                    handleComandaModal={async () => {
                        let codigo = '';
                        await Modal.fire({
                            title: 'Informe a Comanda',
                            html: `<input id="comanda_codigo" class="form-control" placeholder="Código da comanda" />`,
                            confirmButtonText: 'OK',
                            showCancelButton: true,
                            preConfirm: () => {
                                codigo = String((document.getElementById('comanda_codigo') as HTMLInputElement)?.value || '').trim();
                                if (!codigo) {
                                    Modal.showValidationMessage('Informe o código da comanda.');
                                    return false;
                                }
                                return codigo;
                            },
                        });
                        if (!codigo) return;

                        try {
                            const { data: pedido } = await axiosClient.get(`/api/pedidos/comanda/${codigo}`);
                            if (!pedido || !pedido?.id) {
                                Modal.fire({ icon: 'warning', title: 'Alerta', text: 'Comanda não encontrada' });
                                return;
                            }
                            const itens = (pedido.itens || []).map((i: any) => {
                                const produto = i.produto || {};
                                const qtd = Number(i.quantidade || 1);
                                const valor = Number(i.valor || produto.valor_venda || 0);
                                return {
                                    ...produto,
                                    value: produto.id,
                                    label: produto.nome,
                                    id: produto.id,
                                    qtd,
                                    valor_unitario: String(valor),
                                    vl_total: qtd * valor,
                                };
                            });

                            dispatch(setItems(itens));
                            dispatch(setVendaSuspensaId(undefined));
                            dispatch(setPreVendaId(undefined));
                            dispatch(setCodigoComanda(Number(codigo)));
                            Modal.fire({ icon: 'success', title: 'Comanda apontada', timer: 1200, showConfirmButton: false });
                        } catch (e: any) {
                            const msg = e?.response?.data?.message || e?.message || 'Erro ao buscar comanda';
                            Modal.fire({ icon: 'error', title: 'Erro', text: msg });
                        }
                    }}
                    handlePreVendaModal={async () => {
                        try {
                            const { data } = await axiosClient.get('/api/frenteCaixa/pre-vendas', {
                                params: { empresa_id },
                            });
                            const lista = Array.isArray(data) ? data : [];
                            if (!lista.length) {
                                Modal.fire({ icon: 'info', title: 'Pré-vendas', text: 'Nenhuma pré-venda encontrada.' });
                                return;
                            }

                            let selected: any = null;
                            await Modal.fire({
                                title: 'Pré-vendas Recebidas',
                                html: (
                                    <div className="table-responsive">
                                        <table className="table table-striped table-hover">
                                            <thead>
                                            <tr>
                                                <th>Vendedor</th>
                                                <th>Valor</th>
                                                <th>Data</th>
                                                <th>Obs</th>
                                                <th>Ações</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {lista.map((pv: any) => (
                                                <tr key={pv.id}>
                                                    <td>{pv.vendedor_nome}</td>
                                                    <td>{pv.valor_total}</td>
                                                    <td>{pv.created_at}</td>
                                                    <td>{pv.observacao || ''}</td>
                                                    <td>
                                                        <button
                                                            className="btn btn-sm btn-dark"
                                                            onClick={() => {
                                                                selected = pv;
                                                                Modal.clickConfirm();
                                                            }}
                                                        >
                                                            Setar
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ),
                                confirmButtonText: 'Fechar',
                                showCancelButton: true,
                                cancelButtonText: 'Cancelar',
                                preConfirm: () => selected || null,
                                width: 900,
                                customClass: { popup: 'larger-modal' },
                            });
                            if (!selected?.id) return;

                            const { data: detalhes } = await axiosClient.get(`/api/frenteCaixa/pre-vendas/${selected.id}`, {
                                params: { empresa_id },
                            });
                            const itens = (detalhes?.itens || []).map((i: any) => {
                                const produto = i.produto || {};
                                const qtd = Number(i.quantidade || 1);
                                const valor = Number(i.valor || produto.valor_venda || 0);
                                return {
                                    ...produto,
                                    value: produto.id,
                                    label: produto.nome,
                                    id: produto.id,
                                    qtd,
                                    valor_unitario: String(valor),
                                    vl_total: qtd * valor,
                                };
                            });
                            dispatch(setItems(itens));
                            dispatch(setPreVendaId(Number(selected.id)));
                            dispatch(setCodigoComanda(undefined));
                        } catch (e: any) {
                            const msg = e?.response?.data?.message || e?.message || 'Erro ao carregar pré-vendas';
                            Modal.fire({ icon: 'error', title: 'Erro', text: msg });
                        }
                    }}
                />
            ),
            width: 600,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: { popup: 'larger-modal' },

        });
    }, [
        items,
        teclas_atalhos,
        openFinishedOSModal,
        openSalesSuspended,
        lastSales,
        handleSangriaModal,
        handleSuprimentoModal,
        handleFecharCaixaModal,
        handleOrdemServicoModal,
        openScannerModal,
        suspendSale,
    ]);

    const openFinishSaleModal = async () => {
        /* … código inalterado … */
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
                    icon: 'error',
                    title: 'Erro!',
                    text: `Não foi possível registrar esta venda! ${(e as any).response.data}`,
                });
            }
            return;
        }
        dispatch(finishPage());
    };

    const handleBackHome = () => {
        window.location.href = '/';
    }

    const finalizarDisabled =
        (vendedor_obrigatorio !== 0 && !seller?.id) || !items.length;

    useEffect(() => {
        const onKeyDown = (e: KeyboardEvent) => {
            const tag = (e.target as HTMLElement).tagName;
            if (/INPUT|SELECT|TEXTAREA/.test(tag)) return;

            const key = e.key;
            const lower = key.toLowerCase();

            if (key === 'F1' && teclas_atalhos?.includes('scanner_produtos')) {
                e.preventDefault();
                openScannerModal();
            } else if (key === 'F2' && teclas_atalhos?.includes('devolucao')) {
                e.preventDefault();
                suspendSale();
            } else if (key === 'F3' && teclas_atalhos?.includes('trocas')) {
                e.preventDefault();
                dispatch(setTrocasOpen(true));
            } else if (key === 'F4' && teclas_atalhos?.includes('venda_consignada')) {
                e.preventDefault();
                if (items.length) suspendSale();
            } else if (key === 'F5' && teclas_atalhos?.includes('pre_venda')) {
                e.preventDefault();
                suspendSale();
            } else if (key === 'F6' && teclas_atalhos?.includes('abrir_gaveta')) {
                e.preventDefault();
                suspendSale();
            } else if (key === 'F7' && teclas_atalhos?.includes('suspender_vendas')) {
                if (!items.length) return;
                e.preventDefault();
                suspendSale();
            } else if (key === 'F8' && teclas_atalhos?.includes('vendas_suspensas')) {
                e.preventDefault();
                openSalesSuspended();
            } else if (key === 'F9' && teclas_atalhos?.includes('resumo_de_vendas')) {
                e.preventDefault();
                lastSales();
            } else if (key === 'F10' && teclas_atalhos?.includes('convenios_e_limite')) {
                e.preventDefault();
                suspendSale();
            } else if (key === 'F11' && teclas_atalhos?.includes('cancelar_cupom')) {
                e.preventDefault();
                suspendSale();
            } else if (key === 'F12' && teclas_atalhos?.includes('consultar_cashback')) {
                e.preventDefault();
                suspendSale();
            } else if (e.ctrlKey && lower === 'r' && teclas_atalhos?.includes('recarga_celular')) {
                e.preventDefault();
                suspendSale();
            } else if (e.ctrlKey && lower === 'p' && teclas_atalhos?.includes('reimpressao_de_nfc_e')) {
                e.preventDefault();
                suspendSale();
            } else if (e.ctrlKey && lower === 's' && teclas_atalhos?.includes('suprimento')) {
                e.preventDefault();
                handleSuprimentoModal();
            } else if (e.ctrlKey && lower === 'g' && teclas_atalhos?.includes('sangria')) {
                e.preventDefault();
                handleSangriaModal();
            } else if (e.altKey && lower === 'p' && teclas_atalhos?.includes('pagamentos_ordem_servico')) {
                e.preventDefault();
                openFinishedOSModal();
            } else if (e.altKey && lower === 'o' && teclas_atalhos?.includes('ordem_servico')) {
                e.preventDefault();
                handleOrdemServicoModal();
            } else if (e.ctrlKey && lower === 'f' && teclas_atalhos?.includes('fechar_caixa')) {
                e.preventDefault();
                handleFecharCaixaModal();
            }
        };

        document.addEventListener('keydown', onKeyDown);
        return () => document.removeEventListener('keydown', onKeyDown);
    }, [
        teclas_atalhos,
        openScannerModal,
        suspendSale,
        openSalesSuspended,
        lastSales,
        handleSuprimentoModal,
        handleSangriaModal,
        openFinishedOSModal,
        handleOrdemServicoModal,
        handleFecharCaixaModal,
        items.length,
    ]);

    /* ------------------------- RENDER ------------------------------- */
    return (
        <aside className={`sidebar ${isOpen ? '' : 'close'}`}>
            {/* LOGO & TOGGLE */}
            <div className="logo-details">
                <button
                    type="button"
                    className="btn-menu-expanded expanded-menu d-flex align-items-center justify-content-center"
                    onClick={toggleSidebar}
                    title={isOpen ? 'Recolher' : 'Expandir'}
                >
                    {isOpen
                        ? <AnglesRightIcon width={16} height={16} />
                        : <AnglesLeftIcon  width={16} height={16} />}
                </button>
            </div>

            {/* LINKS PRINCIPAIS */}
            <ul className="nav-links">
                <div className="main-navs">
                    {/* Vendedor */}
                    <li>
                        <a href="#" onClick={handleVendedor}>
                            <i className="bx bx-user-pin" />
                            <span className="link_name">Vendedor</span>
                        </a>
                    </li>

                    {/* Cliente */}
                    <li>
                        <a href="#" onClick={handleClient}>
                            <i className="bx bx-user" />
                            <span className="link_name">Cliente</span>
                        </a>
                    </li>

                    {/* Ações */}
                    <li>
                        <a href="#" onClick={openActionPanelModal}>
                            <i className="bx bx-dialpad" />
                            <span className="link_name">Ações</span>
                        </a>
                    </li>


                    {/* Atalhos */}
                    <li>
                        <a href="#" onClick={openShortcutsModal}>
                            <i className="bx bx-help-circle" />
                            <span className="link_name">Atalhos</span>
                        </a>
                    </li>

                    {/* Voltar para home (somente para adms) */}
                    {user.admin === 1 && (
                        <li>
                            <a href="#" 
                            onClick={handleBackHome}
                            >
                                <i className="bx bx-home" />
                                <span className="link_name">Voltar para home</span>
                            </a>
                        </li>
                    )}
                    
                    <li>
                        <a href="#" onClick={handleFecharCaixaModal}>
                            <i className="bx bx-lock" />
                            <span className="link_name">Fechar o caixa</span>
                        </a>
                    </li>
                </div>

                {/* Sair */}
                <li className="logout-link">
                    <a href="#" onClick={handleCloseShiftAndLogout}>
                        <i className="bx bx-power-off" />
                        <span className="link_name">Sair</span>
                    </a>
                </li>
            </ul>
        </aside>
    );
};

export default Sidebar;
