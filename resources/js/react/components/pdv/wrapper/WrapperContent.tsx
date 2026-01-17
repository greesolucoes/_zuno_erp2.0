import { FC, useCallback, useEffect, useRef } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { IStore } from '../../..//store';
import {
    calculateGlobalTotal,
    getPayloadToSuspendSale,
    removeItem,
    setItemSelecionado,
    valorTotal
} from '../../../store/slices/pdvSlice';
import '../../../style/style.css';
import { Item, Vendedor } from '../../../types';
import ProductTable from './ProductTable';
// @ts-ignore

const WrapperContent: FC<{
    usuario_id: number;
    empresa_id: number;
    logo: string;
    banner: string;
    vendedores?: Vendedor[];
}> = ({
          usuario_id,
          empresa_id,
          logo,
          banner
      }) => {
    const logo_prefix = 'https://homolog-diprosoft.s3.us-east-1.amazonaws.com/uploads/logos/';

    const dispatch = useDispatch();

    const qtdRef = useRef<HTMLInputElement | null>(null);

    const items = useSelector((state: IStore) => state.store.items || []);
    const itemSelecionado = useSelector(
        (state: IStore) => state.store.item_selecionado || null,
    );
    const total = useSelector(calculateGlobalTotal);
    const payloadToSuspendSale = useSelector(
        getPayloadToSuspendSale(empresa_id, usuario_id, total),
    );

    useEffect(() => {
        if (itemSelecionado) {
            const valor_unitario =
                parseFloat(itemSelecionado.valor_unitario) || 0;
            const quantidade = itemSelecionado.qtd || 0;
            const valor_total = valor_unitario * quantidade;
            if (qtdRef.current) qtdRef.current.value = quantidade.toFixed(3);
            dispatch(valorTotal(valor_total));
        }
    }, [itemSelecionado?.qtd, itemSelecionado?.valor_unitario, dispatch]);

    const editItemOnClick = useCallback(
        (item: Item) => () =>
            dispatch(setItemSelecionado({ ...item, isEditing: true })),
        [dispatch],
    );

    return (

        <section id="main__content" className="main-content">
            <ProductTable
                items={items}
                onEditItem={(item) => editItemOnClick(item)()}
                onRemoveItem={(id) => dispatch(removeItem(id))}
                logo={logo}
            />

            {(
                banner.trim() !== '' && !banner.includes('/imgs/no-image.png')
            ) || (
                logo && 
                logo.substring(logo_prefix.length).length > 0 && 
                !logo.substring(logo_prefix.length).includes('/')
            ) ? (
                <img
                    src={
                    banner.trim() !== '' && !banner.includes('/imgs/no-image.png')
                        ? banner
                        : logo
                    }
                    alt="Banner promocional"
                    className="banner-img"
                />
            ) : null}
        </section>


    );
};

export default WrapperContent;
