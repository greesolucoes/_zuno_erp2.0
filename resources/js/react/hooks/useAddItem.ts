import { useCallback } from 'react';
import { useDispatch } from 'react-redux';
import { setItems, setItemSelecionado } from '../store/slices/pdvSlice';
import { Item } from '../types';

export default function useAddItem(
    items: Item[],
    isEditing: boolean,
    itemSelecionado: Item | null,
) {
    const dispatch = useDispatch();

    const checkProdutoIsAtacado = (produto: Item): boolean => {
        if (produto && produto.quantidade_atacado && parseFloat(produto.quantidade_atacado) > 0) {
            if (produto.qtd && produto.qtd >= parseFloat(produto.quantidade_atacado)) {
                return true;
            }
        }

        return false;
    }

    return useCallback(
        async (prop: Item | null) => {
            if (itemSelecionado || prop) {
                try {
                    const selecionado = (prop || itemSelecionado) as Item;
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
                                            Number(item.qtd || 0) +
                                            (selecionado.qtd || 0),
                                        vl_total:
                                            (checkProdutoIsAtacado(item) ? parseFloat(item.valor_atacado) : parseFloat(item.valor_unitario)) *
                                            (
                                                Number(item.qtd || 0) + (selecionado.qtd || 0)
                                            ),
                                        is_atacado: checkProdutoIsAtacado(item),
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
                                (checkProdutoIsAtacado(selecionado) ? parseFloat(selecionado.valor_atacado) : parseFloat(selecionado.valor_unitario)) *
                                (selecionado.qtd || 0),
                            is_atacado: checkProdutoIsAtacado(selecionado),
                        };
                        dispatch(setItems([...items, newItem]));
                    }
                } catch (e) {
                    console.error(e);
                }
                dispatch(setItemSelecionado(null));
            }
        },
        [itemSelecionado, items, isEditing, dispatch],
    );
}
