import { FC, useCallback, useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import type { InputActionMeta } from 'react-select';
import { Modal } from '../../../constants';
import { formatToCurrency, parseCurrencyToFloat } from '../../../helpers';
import useAddItem from '../../../hooks/useAddItem';
import { validateProductStock } from '../../../services/stockService';
import { IStore } from '../../../store';
import { setItemSelecionado } from '../../../store/slices/pdvSlice';
import { Item, ProdutoResponse } from '../../../types';
import Autocomplete from '../../shared/Autocomplete';
// @ts-ignore – imagem de fallback

interface Select2ProdutoProps {
    path: string;
    usuario_id: number;
    empresa_id: number;
    filial_id?: number | string | null;
}

const parseProduto = (item: ProdutoResponse) => {
    const valorUnitario = (item as any).valor_unitario ?? (item as any).valor_venda ?? '0';
    const estoqueAtual = (item as any).estoque_atual ?? (item as any).estoqueAtual ?? 0;
    const unidade = (item as any).unidade ?? (item as any).unidade_venda ?? 'UN';
    const codigoBarras = (item as any).codigo_barras ?? (item as any).codBarras ?? null;
    let label = item.nome;
    if (item?.combinacoes) label += ` ${item.combinacoes}`;
    if (parseFloat(String(valorUnitario)) > 0) {
        label += `${formatToCurrency(valorUnitario)}`;
    }
    const estoqueView = document.getElementById('estoque_view');
    const currentStock = Number(estoqueAtual ?? 0);
    if (
        currentStock > 0 &&
        estoqueView &&
        (estoqueView as HTMLInputElement).value === '1'
    ) {
        label += ` | Estoque: ${currentStock}`;
    }
    if (codigoBarras) label += ` [${codigoBarras}]`;
    if (item?.codigo) label += ` [${item.codigo}]`;
    return {
        value: item.id,
        label,
        ...item,
        valor_unitario: String(valorUnitario),
        estoque_atual: Number(estoqueAtual ?? 0),
        unidade: String(unidade || 'UN'),
        codigo_barras: codigoBarras,
        qtd: 1,
    } as unknown as Item;
};

const Select2Produto: FC<Select2ProdutoProps> = ({
                                                     path,
                                                     usuario_id,
                                                     empresa_id,
                                                     filial_id,
                                                 }) => {
    const itemSelecionado = useSelector(
        (state: IStore) => state.store.item_selecionado,
    );
    const [selectedOption, setSelectedOption] = useState<unknown | null>(null);
    const [searchValue, setSearchValue] = useState('');

    useEffect(() => {
        if (!itemSelecionado) {
            setSelectedOption(null);
            setSearchValue('');
        } else {
            setSelectedOption(itemSelecionado);
        }
    }, [itemSelecionado]);

    useEffect(() => {
        if (typeof window === 'undefined') return;

        const clearSearchValue = () => setSearchValue('');
        window.addEventListener('pdv:barcode-scanned', clearSearchValue);

        return () => {
            window.removeEventListener('pdv:barcode-scanned', clearSearchValue);
        };
    }, []);

    const dispatch = useDispatch();

    const items = useSelector((state: IStore) => state.store.items || []);
    const isEditing = useSelector((state: IStore) => state.store.isEditing);
    const handleAddItem = useAddItem(
        items,
        isEditing,
        itemSelecionado as Item | null,
    );

    const handleChange = useCallback(
        async (option: Item | null) => {
            if (!option) return;
            const valorUnitario = (option as any).valor_unitario ?? (option as any).valor_venda ?? '0';
            if (parseFloat(String(valorUnitario) || '0') <= 0) {
                await Modal.fire({
                    icon: 'warning',
                    title: 'produto sem valor de venda',
                });
                return;
            }

            let quantidade = 1;
            let { isConfirmed, value } = await Modal.fire({
                html: (
                    <section id="product_confirm_modal">
                        <div className="d-flex align-items-start gap-4 mt-3">
                            <div>
                                <img
                                    src={
                                        option.imgApp
                                    }
                                    alt={`Foto de ${option.nome}`}
                                    className="product-photo"
                                />
                            </div>
                            <div className="d-flex flex-column align-items-start gap-4">
                                <h3>{option.nome}</h3>
                                <div className='fs-3 text-purple'>
                                    <span className='fw-semibold'>Valor do produto: </span>{formatToCurrency(option.valor_unitario)}
                                </div>
                                {option.descricao && <p>{option.descricao}</p>}
                                {option.gerenciar_estoque == 1 && (
                                    <div className='fs-4 fw-light text-purple'><b>Estoque atual:</b> {Number(option.estoque_atual)}</div>
                                )}
                                {option.gerenciar_estoque == 0 && (
                                    <div className='fs-4 fw-light text-purple'>Produto com o estoque não gerenciável</div>
                                )}
                            </div>
                        </div>
                        <div className='my-4 gap-3 d-flex align-items-center justify-content-center gap-3'>
                            <div>
                                <label className="me-2 fs-2 fw-semibold text-purple" htmlFor="modal_qtd">
                                    Quantidade:
                                </label>
                            </div>
                            <input
                                id="modal_qtd"
                                type="number"
                                min="1"
                                defaultValue={1}
                                className="form-control d-inline-block quantity-input"
                                style={{ width: '70px' }}
                                autoFocus
                                onKeyDown={(e) => {
                                    if (e.key.toLowerCase() === 'enter') {
                                        if (parseInt(e.currentTarget.value) > 0 && e.currentTarget.value != '') {
                                            Modal.clickConfirm();
                                        }
                                    }
                                }}
                            />
                        </div>
                        {
                            (option.quantidade_atacado ? parseFloat(option.quantidade_atacado) > 0 : false) &&
                            option.valor_atacado && (
                                <div className='d-flex flex-column align-items-start justify-content-center gap-4'>
                                    <div className='fs-3'>
                                        <b>Quantidade mínima para o atacado:</b> {option.quantidade_atacado}
                                    </div> 
                                    <div className='fs-3'>
                                        <b>Valor unitário do atacado:</b> {parseFloat(option.valor_atacado).toFixed(2).replace('.', ',')}
                                    </div>
                                </div>
                            )
                        }
                    </section>
                ),
                focusConfirm: false,
                confirmButtonText: 'Confirmar',
                preConfirm: () => {
                    const el = document.getElementById(
                        'modal_qtd',
                    ) as HTMLInputElement;
                    const q = parseFloat(el?.value || '1');
                    return isNaN(q) || q <= 0 ? 1 : q;
                },
                customClass: { popup: 'larger-modal' },
            });

            if (!isConfirmed) return;

            quantidade = Number(value) || 1;

            try {
                await validateProductStock(option.id, quantidade, usuario_id, filial_id, empresa_id);
            } catch (e: any) {
                await Modal.fire({
                    icon: 'warning',
                    title: 'Produto sem estoque',
                    text: e.message,
                });
                return;
            }

            const payload = { ...option, qtd: quantidade } as Item;
            dispatch(setItemSelecionado(payload));
            await handleAddItem(payload);
        },
        [dispatch, handleAddItem],
    );
    return (
        <Autocomplete
            id={'pdv-product-search'}
            selectedOption={selectedOption}
            path={path}
            parseResponse={parseProduto}
            placeholder={'Digite o código ou passe o produto'}
            onChangeCallback={handleChange}
            inputValue={searchValue}
            onInputChange={(value: string, actionMeta: InputActionMeta) => {
                if (actionMeta.action === 'input-change') {
                    setSearchValue(value);
                    return;
                }

                if (
                    actionMeta.action === 'set-value' ||
                    actionMeta.action === 'input-blur' ||
                    actionMeta.action === 'menu-close'
                ) {
                    setSearchValue('');
                }
            }}
            params={{
                lista_id: '',
                usuario_id: String(usuario_id),
                empresa_id: String(empresa_id),
                filial_id: String(filial_id ?? ''),
            }}
        />
    );
};

export default Select2Produto;
