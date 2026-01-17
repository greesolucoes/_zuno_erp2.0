import { axiosClient, Modal } from '../constants';
import { parseCurrencyToFloat, parseItem } from '../helpers';
import { validateProductStock } from './stockService';
import { Item, ProdutoResponse } from '../types';

async function ensureStockOrWarn(
    productId: number,
    qtd: number,
    usuario_id: number,
    filial_id: number | string | null | undefined,
    empresa_id: number,
) {
    try {
        await validateProductStock(productId, qtd, usuario_id, filial_id, empresa_id);
        return true;
    } catch (e: any) {
        await Modal.fire({
            icon: 'warning',
            title: 'Produto sem estoque',
            text: e.message,
        });
        return false;
    }
}

export async function fetchProductByReference(
    barcode: string,
    empresa_id: number,
    usuario_id: number,
    filial_id?: number | string | null,
) {
    try {
        const { data } = await axiosClient.get('/api/produtos/findByBarcodeReference', {
            params: { barcode, empresa_id, usuario_id, filial_id },
        });
        return data;
    } catch (error) {
        console.error('Erro ao buscar produto por referência:', error);
    }
    return false;
}

export async function fetchProductByBarcode(
    barcode: string,
    empresa_id: number,
    usuario_id: number,
    filial_id: number | string | null | undefined,
    handleAddItem: (item: Item) => Promise<void>,
) {
    if (barcode.length <= 7) return;
    try {
        const response = await axiosClient.get('/api/produtos/findByBarcode', {
            params: { barcode, empresa_id, lista_id: '', usuario_id, filial_id },
        });
        const { data } = response;
        if (data.valor_unitario) {
            if (parseCurrencyToFloat(data?.valor_unitario || '0')) {
                const okStock = await ensureStockOrWarn(
                    data.id,
                    1,
                    usuario_id,
                    filial_id,
                    empresa_id,
                );
                if (!okStock) return;
                await handleAddItem(parseItem(data));
                setTimeout(() => {
                    document.getElementById('add')?.click();
                }, 500);
            } else {
                await Modal.fire({ icon: 'warning', title: 'produto sem valor de venda' });
            }
        } else {
            const produto = await fetchProductByReference(barcode, empresa_id, usuario_id, filial_id);
            if (produto) {
                if (parseCurrencyToFloat(produto?.valor_unitario || '0')) {
                    const okStock = await ensureStockOrWarn(
                        produto.id,
                        1,
                        usuario_id,
                        filial_id,
                        empresa_id,
                    );
                    if (!okStock) return;
                    await handleAddItem(parseItem(produto));
                } else {
                    await Modal.fire({ icon: 'warning', title: 'produto sem valor de venda' });
                }
            } else {
                await Modal.fire({ icon: 'error', title: 'Erro ao buscar produto!' });
            }
        }
    } catch (error) {
        console.error('Erro ao buscar produto por código de barras:', error);
        const produto = await fetchProductByReference(barcode, empresa_id, usuario_id, filial_id);
        if (produto) {
            if (parseCurrencyToFloat(produto?.valor_unitario || '0')) {
                const okStock = await ensureStockOrWarn(
                    produto.id,
                    1,
                    usuario_id,
                    filial_id,
                    empresa_id,
                );
                if (!okStock) return;
                await handleAddItem(parseItem(produto));
            } else {
                await Modal.fire({ icon: 'warning', title: 'produto sem valor de venda' });
            }
        } else {
            await Modal.fire({ icon: 'error', title: 'Erro ao buscar produto!' });
        }
    }
}
export async function fetchProductDataByBarcode(
    barcode: string,
    empresa_id: number,
    usuario_id: number,
    filial_id?: number | string | null,
): Promise<ProdutoResponse | null> {
    if (barcode.length <= 7) return null;
    try {
        const { data } = await axiosClient.get('/api/produtos/findByBarcode', {
            params: { barcode, empresa_id, lista_id: '', usuario_id, filial_id },
        });
        if (data.valor_unitario) {
            return data as ProdutoResponse;
        }
        const produto = await fetchProductByReference(
            barcode,
            empresa_id,
            usuario_id,
            filial_id,
        );
        return (produto as ProdutoResponse) || null;
    } catch (error) {
        console.error('Erro ao buscar produto por código de barras:', error);
        const produto = await fetchProductByReference(barcode, empresa_id, usuario_id, filial_id);
        return (produto as ProdutoResponse) || null;
    }
}
