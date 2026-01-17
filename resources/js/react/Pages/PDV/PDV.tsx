import { FC, useCallback, useEffect, useState } from 'react';
import 'bootstrap/dist/css/bootstrap.css';
import '../../style/style.css';
import Footer from '../../components/pdv/footer/Footer';
import TotalToPayButton from '../../components/pdv/footer/TotalToPayButton'; // no topo
import PDVHeader from '../../components/pdv/header/PDVHeader';
import Select2Produto from '../../components/pdv/product/Select2Produto';
import Sidebar from '../../components/pdv/sidebar/Sidebar';
import WrapperContent from '../../components/pdv/wrapper/WrapperContent';
import { useDispatch, useSelector } from 'react-redux';
import { axiosClient } from '../../constants';
import { parseToItems, reorganizarPorValor } from '../../helpers';
import useAxiosLoader from '../../hooks/useAxiosLoader';
import useBarcode from '../../hooks/useBarcode';
import useProductScannerModal from '../../hooks/useProductScannerModal';
import { IStore } from '../../store';
import {
    resetState,
    setClient,
    setItems,
    setItemSelecionado,
    handleDiscount,
    setDescontoTipo,
    setTrocasOpen,
    setVendaSuspensaId,
} from '../../store/slices/pdvSlice';
import { FormaPagamentoDetalhe, Item, ResponseData, TiposPagamento, Venda } from '../../types';
import Finish from '../Finish';
import TrocasModal from '../../components/pdv/trocas/TrocasModal';
console.log('PDV page loaded global style.css');

const PDV: FC<ResponseData> = ({
                                   operador_nome,
                                   caixa_fisico,
                                   local,
                                   user,
                                   abertura: { id: id_abertura },
                                   funcionarios = [],
                                   item,
                                   tiposPagamento = {},
                                   config: { vendedor_obrigatorio = 0 } = { vendedor_obrigatorio: 0 },
                                   vendedores,
                                   teclas_atalhos = [],
                                   bandeiras = {},
                                   detalhesFormas = [],
                                   banner = '',
                               }) => {
    const usuario_id = user.id;
    const empresa_id = user.empresa.empresa.id;
    const logo = user.empresa.empresa.logo;
    const filial_id = local?.id ?? null;
    const isNaoFiscal = Boolean(user?.empresa?.empresa?.nao_fiscal);
    const segmento = user?.empresa?.empresa?.plano?.plano?.segmento?.nome;

    const dispatch = useDispatch();
    const finish = useSelector((state: IStore) => Boolean(state.store.finish));
    const trocasOpen = useSelector((state: IStore) => Boolean(state.store.trocasOpen));
    const paymentUrl = useSelector((state: IStore) => state.store.paymentUrl);
    const ordemServicoId = useSelector(
        (state: IStore) => state.store.ordemServicoId,
    );
    const ordemServicoHasProduto = useSelector(
        (state: IStore) => state.store.ordemServicoHasProduto,
    );
    const ordemServicoHasServico = useSelector(
        (state: IStore) => state.store.ordemServicoHasServico,
    );
    const items = useSelector((state: IStore) => state?.store?.items || []);
    const cliente = useSelector((state: IStore) => state?.store?.cliente);
    const isEditing = useSelector((state: IStore) => state.store.isEditing);
    const [paymentTypes, setPaymentTypes] = useState<TiposPagamento>(tiposPagamento);
    const [paymentDetails, setPaymentDetails] = useState<FormaPagamentoDetalhe[]>(detalhesFormas);

    const handleAddItem = useCallback(
        async (prop: Item | null) => {
            if (prop) {
                try {
                    const selecionado = prop as Item;
                    const existingItemIndex = items.findIndex(
                        (item) => item.id === selecionado.id,
                    );
                    console.log(selecionado.qtd);
                    if (existingItemIndex >= 0) {
                        if (isEditing) {
                            const newItens = [
                                ...items.filter(
                                    (item) => item.id !== selecionado.id,
                                ),
                                selecionado,
                            ] as Item[];
                            dispatch(setItems(newItens));
                        } else {
                            const updatedItems = items.map((item, index) => {
                                if (index === existingItemIndex) {
                                    return {
                                        ...item,
                                        qtd:
                                            Number(item?.qtd || 0) +
                                            (selecionado.qtd || 0),
                                        vl_total:
                                            Number(item?.valor_unitario || 0) *
                                            (Number(item?.qtd || 0) +
                                                (selecionado.qtd || 0)),
                                    };
                                }
                                return item;
                            });
                            dispatch(setItems(updatedItems));
                        }
                    } else {
                        const newItem = {
                            ...selecionado,
                            vl_total:
                                (parseFloat(selecionado.valor_unitario) || 0) *
                                (selecionado.qtd || 0),
                        };

                        dispatch(setItems([...items, newItem]));
                    }
                } catch (e) {
                    console.log(e);
                }
                dispatch(setItemSelecionado(null));
            }
        },
        [items, isEditing],
    );
    const openScannerModal = useProductScannerModal(
        empresa_id,
        usuario_id,
        filial_id,
        handleAddItem,
    );
    useBarcode({ empresa_id, usuario_id, filial_id, handleAddItem });

    useEffect(() => {
        localStorage.clear();
        dispatch(resetState());
        axiosClient
            .get('/api/pdv/tipos-pagamento', { params: { usuario_id } })
            .then(({ data }) => {
                const mapped: TiposPagamento = {};
                (data.lista || []).forEach((item: { id: string; nome: string }) => {
                    if (item.id) mapped[item.id] = item.nome;
                });
                setPaymentTypes(mapped);
                setPaymentDetails(data.detalhes || []);
            })
            .catch((err) => {
                console.error('Erro ao carregar formas de pagamento', err);
            });
        if (item && item?.length > 0) {
            const { cliente, ...suspendedSale } = item[0] as Venda;
            const items: Item[] = parseToItems(suspendedSale);
            dispatch(setItems(items));
            // Mantém o ID da venda suspensa para o backend conseguir finalizar e vincular Troca ↔ NFC-e nova.
            if ((suspendedSale as any)?.id) {
                dispatch(setVendaSuspensaId((suspendedSale as any).id));
            }
            if (cliente) {
                dispatch(setClient(cliente));
            }
        }
    }, []);

    useEffect(() => {
        if (!usuario_id) return;
        if (!cliente?.id) return;

        axiosClient
            .get('/api/pdv/tipos-pagamento', { params: { usuario_id, cliente_id: cliente.id } })
            .then(({ data }) => {
                const mapped: TiposPagamento = {};
                (data.lista || []).forEach((item: { id: string; nome: string }) => {
                    if (item.id) mapped[item.id] = item.nome;
                });
                setPaymentTypes(mapped);
                setPaymentDetails(data.detalhes || []);
            })
            .catch(() => {});
    }, [usuario_id, cliente?.id]);

    useAxiosLoader();

    if (!finish) {
        return (
            <main className="main">
                <div className="header-area">
                    <PDVHeader caixaFisico={caixa_fisico} local={local} operator={operador_nome} segmento={segmento} isNaoFiscal={isNaoFiscal}>
                        <Select2Produto
                            path={'api/produtos'}
                            usuario_id={usuario_id}
                            empresa_id={empresa_id}
                            filial_id={filial_id}
                        />
                    </PDVHeader>
                </div>
                <div className="content-area">
                    <WrapperContent
                        {...{
                            id_abertura,
                            usuario_id,
                            empresa_id,
                            logo,
                            banner,
                            vendedores,
                            teclas_atalhos,
                        }}
                    />
                </div>

                <div className="sidebar-area">
                    <Sidebar
                        {...{
                            user,
                            empresa_id,
                            usuario_id,
                            tiposPagamento: reorganizarPorValor(paymentTypes),
                            vendedor_obrigatorio,
                            vendedores,
                            id_abertura,
                            teclas_atalhos,
                            openScannerModal,
                        }}
                    />
                    <div className="floating-total-wrapper">
                        <TotalToPayButton />
                    </div>
                    <Footer
                        {...{
                            funcionarios,
                            empresa_id,
                            usuario_id,
                            operador_nome,
                            tiposPagamento: reorganizarPorValor(paymentTypes),
                            vendedor_obrigatorio,
                            vendedores,
                            id_abertura,
                            teclas_atalhos,
                        }}
                    />
                </div>
                <TrocasModal
                    open={trocasOpen}
                    onClose={() => dispatch(setTrocasOpen(false))}
                    empresaId={empresa_id}
                    usuarioId={usuario_id}
                    paymentTypes={reorganizarPorValor(paymentTypes)}
                    onApplyCreditToCart={(value) => {
                        dispatch(setDescontoTipo('R$'));
                        dispatch(handleDiscount(Number(value || 0)));
                        dispatch(setTrocasOpen(false));
                    }}
                />
            </main>
        );
    }

    return (
        <Finish
            tiposPagamento={reorganizarPorValor(paymentTypes)}
            detalhesFormas={paymentDetails}

            {...{ empresa_id, usuario_id, bandeiras, isNaoFiscal }}
            url={paymentUrl}
            ordem_servico_id={ordemServicoId}
            ordem_servico_has_produto={ordemServicoHasProduto}
            ordem_servico_has_servico={ordemServicoHasServico}
            caixa_id={id_abertura}
        >
            <PDVHeader caixaFisico={caixa_fisico} local={local} operator={operador_nome} segmento={segmento} isFinish={finish} isNaoFiscal={isNaoFiscal} />
        </Finish>
    );
};
PDV.page = {
    title: 'Frente de Caixa',
};
export default PDV;
