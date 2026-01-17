import 'bootstrap/dist/css/bootstrap.css';
import '../../style/style.css';
import { FC, useCallback, useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import Footer from '../../components/pdv/footer/Footer';
import PDVHeader from '../../components/pdv/header/PDVHeader';
import Select2Produto from '../../components/pdv/product/Select2Produto';
import WrapperContent from '../../components/pdv/wrapper/WrapperContent';
import { axiosClient, Modal } from '../../constants';
import {
    appendLoadingElement,
    parseCurrencyToFloat,
    parseItem,
    parseToItems,
    removeLoadingElement,
    reorganizarPorValor,
} from '../../helpers';
import { IStore } from '../../store';
import {
    resetState,
    setClient,
    setItems,
    setItemSelecionado,
} from '../../store/slices/pdvSlice';
import { FormaPagamentoDetalhe, Item, ResponseData, TiposPagamento, Venda } from '../../types';
import Finish from '../Finish';
console.log('PreVenda page loaded global style.css');
const PDV: FC<ResponseData> = ({
                                   caixa_fisico,
                                   local,
                                   operador_nome,
                                   user: {
                                       id: usuario_id,
                                       empresa: { empresa_id, empresa: { logo = '', nao_fiscal = false } = {} },
                                   },
                                   abertura: { id: id_abertura },
                                   funcionarios = [],
                                   item,
                                   tiposPagamento = {},
                                   config: { vendedor_obrigatorio = 0 } = { vendedor_obrigatorio: 0 },
                                   vendedores,
                                   teclas_atalhos = [],
                                   bandeiras = {},
                                   detalhesFormas = [],
                                   banner = ''
                               }) => {
    const isNaoFiscal = Boolean(nao_fiscal);
    const filial_id = local?.id ?? null;
    const dispatch = useDispatch();
    const [barcode, setBarcode] = useState(''); // Armazena os caracteres lidos
    const finish = useSelector((state: IStore) => Boolean(state.store.finish));
    const [lastInputTime, setLastInputTime] = useState(Date.now()); // Armazena o tempo do último caractere
    const items = useSelector((state: IStore) => state?.store?.items || []);
    const cliente = useSelector((state: IStore) => state?.store?.cliente);
    const [paymentTypes, setPaymentTypes] = useState<TiposPagamento>(tiposPagamento);
    const [paymentDetails, setPaymentDetails] = useState<FormaPagamentoDetalhe[]>(detalhesFormas);
    const isEditing = useSelector((state: IStore) => state.store.isEditing);
    const handleAddItem = useCallback(
        async (prop: Item | null) => {
            if (prop) {
                try {
                    const selecionado = prop as Item;
                    const existingItemIndex = items.findIndex(
                        (item) => item.id === selecionado.id,
                    );

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
    const barcodeTimeout = 70; // Tempo máximo entre teclas para considerar como leitura (ms)
    // Função para buscar produto por código de barras

    async function getProductByBarcode(barcode: string) {
        if (barcode.length > 7) {
            try {
                const response = await axiosClient.get(
                    '/api/produtos/findByBarcode',
                    {
                        params: {
                            barcode,
                            empresa_id,
                            lista_id: '',
                            usuario_id,
                        },
                    },
                );

                const { data } = response;

                if (data.valor_unitario) {
                    if (parseCurrencyToFloat(data?.valor_unitario || '0')) {
                        const payload = parseItem(data);
                        await handleAddItem(payload);

                        // Auto-adiciona o item ao carrinho
                        setTimeout(() => {
                            const buttonAdd = document.getElementById('add');
                            if (buttonAdd) {
                                buttonAdd?.click();
                            }
                        }, 500);
                    } else {
                        await Modal.fire({
                            icon: 'warning',
                            title: 'produto sem valor de venda',
                        });
                    }
                } else {
                    const data = await buscarPorReferencia(barcode);
                    if (data) {
                        if (parseCurrencyToFloat(data?.valor_unitario || '0')) {
                            const payload = parseItem(data);
                            //dispatch(setItemSelecionado(payload));
                            await handleAddItem(payload);
                        } else {
                            await Modal.fire({
                                icon: 'warning',
                                title: 'produto sem valor de venda',
                            });
                        }
                    } else {
                        await Modal.fire({
                            icon: 'error',
                            title: 'Erro ao buscar produto!',
                        });
                    }
                }
            } catch (error) {
                console.error(
                    'Erro ao buscar produto por código de barras:',
                    error,
                );

                const data = await buscarPorReferencia(barcode);
                if (data) {
                    if (parseCurrencyToFloat(data?.valor_unitario || '0')) {
                        await handleAddItem(parseItem(data));
                    } else {
                        await Modal.fire({
                            icon: 'warning',
                            title: 'produto sem valor de venda',
                        });
                    }
                } else {
                    await Modal.fire({
                        icon: 'error',
                        title: 'Erro ao buscar produto!',
                    });
                }
            }
        }
    }

    // Função para buscar produto por referência
    async function buscarPorReferencia(barcode: string) {
        try {
            const response = await axiosClient.get(
                '/api/produtos/findByBarcodeReference',
                {
                    params: {
                        barcode,
                        empresa_id,
                        usuario_id,
                    },
                },
            );

            return response.data;
        } catch (error) {
            console.error('Erro ao buscar produto por referência:', error);
            alert('Erro: Produto não localizado!');
        }
        return false;
    }

    // Processa o buffer do código de barras
    async function processBarcode(code: string) {
        await getProductByBarcode(code);
    }

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

    useEffect(() => {
        const handleKeyDown = async (event: KeyboardEvent) => {
            const currentTime = Date.now();
            const timeDiff = currentTime - lastInputTime;

            // Reseta o buffer se o intervalo entre teclas for maior que o timeout
            if (timeDiff > barcodeTimeout) {
                setBarcode('');
            }

            // Adiciona o caractere ao buffer (ignora teclas especiais)
            if (event?.key?.length === 1) {
                setBarcode((prev) => prev + event.key);
            }

            setLastInputTime(currentTime);

            // Verifica se a leitura foi concluída
            if (
                (event.key === 'Enter' ||
                    event.key === 'Tab' ||
                    event.key === 'ArrowRight' ||
                    event.key === 'ArrowDown' ||
                    event.key === 'ArrowLeft' ||
                    event.key === 'ArrowUp') &&
                barcode.length >= 8 // Ajuste o tamanho mínimo, se necessário
            ) {
                await processBarcode(barcode);
                setBarcode(''); // Limpa o buffer
            }
        };

        // Adiciona escuta do evento
        document.addEventListener('keydown', handleKeyDown);

        // Remove evento ao desmontar o componente
        return () => {
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [barcode, lastInputTime]);

    useEffect(() => {
        const $body = document.body;

        // Interceptores do Axios
        const requestInterceptor = axiosClient.interceptors.request.use(
            (config) => {
                $body.classList.add('loading');
                appendLoadingElement();
                return config;
            },
        );

        const responseInterceptor = axiosClient.interceptors.response.use(
            (response) => {
                $body.classList.remove('loading');
                removeLoadingElement();
                return response;
            },
            (error) => {
                $body.classList.remove('loading');
                removeLoadingElement();
                return Promise.reject(error);
            },
        );

        // Cleanup ao desmontar o componente
        return () => {
            axiosClient.interceptors.request.eject(requestInterceptor);
            axiosClient.interceptors.response.eject(responseInterceptor);
        };
    }, []);

    if (!finish) {
        return (
            <main className="main">
                <PDVHeader caixaFisico={caixa_fisico} local={local} operator={operador_nome} isNaoFiscal={isNaoFiscal}>
                    <Select2Produto
                        path={'api/produtos'}
                        usuario_id={usuario_id}
                        empresa_id={empresa_id}
                        filial_id={filial_id}
                    />
                </PDVHeader>

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
            </main>
        );
    }

    return (
        <Finish
            tiposPagamento={reorganizarPorValor(paymentTypes)}
            detalhesFormas={paymentDetails}
            url={'api/pre-venda/store'}
            {...{ empresa_id, usuario_id, bandeiras, isNaoFiscal }}
            caixa_id={id_abertura}
        >
            <PDVHeader caixaFisico={caixa_fisico} local={local} operator={operador_nome} isFinish={finish} isNaoFiscal={isNaoFiscal} />
        </Finish>
    );
};
PDV.page = {
    title: 'Frente de Caixa',
};
export default PDV;
