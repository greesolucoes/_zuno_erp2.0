/* useProductScannerModal.tsx
 * Substitui BarcodeDetector por react-barcode-scanner
 * --------------------------------------------------- */

import { useDispatch } from 'react-redux';
import ReactDOM from 'react-dom/client';
import { BarcodeScanner } from 'react-barcode-scanner';
import 'react-barcode-scanner/polyfill';

import { Modal } from '../constants';
import { parseCurrencyToFloat, parseItem } from '../helpers';
import { fetchProductDataByBarcode } from '../services/productService';
import { validateProductStock } from '../services/stockService';
import { setItemSelecionado } from '../store/slices/pdvSlice';
import { Item, ProdutoResponse } from '../types';
// @ts-ignore – imagem de fallback

export default function useProductScannerModal(
  empresa_id: number,
  usuario_id: number,
  filial_id: number | string | null | undefined,
  handleAddItem: (item: Item) => Promise<void>,
) {
  const dispatch = useDispatch();

  /* ================================================================
   * Confirma item + quantidade
   * ============================================================== */
  async function confirmAndAdd(product: ProdutoResponse) {
    const { isConfirmed, value } = await Modal.fire({
      html: `
        <section id="product_confirm_modal">
          <img src="${product.imgApp}"
               alt="Foto de ${product.nome}" class="product-photo mb-2" />
          <h3 class="mb-1">${product.nome}</h3>
          ${product.descricao ? `<p>${product.descricao}</p>` : ''}
          <p>Estoque: ${product.estoque_atual ?? '—'}</p>
          <div>
            <label class="me-2" for="modal_qtd">Quantidade:</label>
            <input id="modal_qtd" type="number" min="1" value="1"
                   class="form-control d-inline-block" style="width:80px" />
          </div>
        </section>
      `,
      confirmButtonText: 'Confirmar (ENTER)',
      footer:
        '<small class="text-muted">ENTER confirma · ESC cancela</small>',
      focusConfirm: true,
      allowEnterKey: true,
      allowEscapeKey: true,
      preConfirm: () => {
        const el = document.getElementById('modal_qtd') as HTMLInputElement;
        const qtd = parseFloat(el?.value || '1');
        return isNaN(qtd) || qtd <= 0 ? 1 : qtd;
      },
      customClass: { popup: 'larger-modal' },
    });

    if (!isConfirmed) return;

    const quantidade = Number(value) || 1;
    const payload = { ...parseItem(product), qtd: quantidade } as Item;

    try {
      await validateProductStock(product.id, quantidade, usuario_id, filial_id, empresa_id);
    } catch (e: any) {
      await Modal.fire({
        icon: 'warning',
        title: 'Produto sem estoque',
        text: e.message,
      });
      return;
    }

    dispatch(setItemSelecionado(payload));
    await handleAddItem(payload);

    /* ----------------------------------------------------------------
     * Pergunta: continuar adicionando?
     * -------------------------------------------------------------- */
    const { isConfirmed: again } = await Modal.fire({
      title: 'Adicionar mais produtos?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sim (ENTER)',
      cancelButtonText: 'Não (ESC)',
      focusConfirm: true,
      allowEnterKey: true,
      allowEscapeKey: true,
      footer:
        '<small class="text-muted">Pressione ENTER para continuar ou ESC para sair</small>',
    });

    if (again) await openScannerModal();
  }

  /* ================================================================
   * Scanner de código de barras (react-barcode-scanner)
   * ============================================================== */
  async function openScannerModal() {
    let root: ReactDOM.Root | null = null; // cleanup

    await Modal.fire({
      title: 'Scanner de Produtos',
      html: '<div id="scanner_container" class="scanner"></div>',
      showConfirmButton: false,
      showCloseButton: true,
      width: 400,
      customClass: { popup: 'larger-modal' },

      didOpen: () => {
        const container = document.getElementById('scanner_container')!;
        root = ReactDOM.createRoot(container);

        root.render(
          <>
            <BarcodeScanner
              options={{ formats: ['ean_13', 'ean_8', 'code_128', 'code_39'] }}
              onCapture={async (barcodes) => {
                const code = barcodes?.[0]?.rawValue;
                if (!code) return;

                root?.unmount(); // pausa scanner
                Modal.close();

                try {
                  const product = await fetchProductDataByBarcode(
                    code,
                    empresa_id,
                    usuario_id,
                    filial_id,
                  );

                  if (product && parseCurrencyToFloat(product.valor_unitario || '0')) {
                    await confirmAndAdd(product);
                  } else {
                    await Modal.fire({
                      icon: 'warning',
                      title: 'Produto sem valor de venda ou não encontrado',
                    });
                  }
                } catch {
                  await Modal.fire({
                    icon: 'error',
                    title: 'Erro ao buscar produto!',
                  });
                }
              }}
            />
            {/* Mantém overlay/laser/hint originais */}
            <div className="scanner__overlay"></div>
            <div className="scanner__laser"></div>
            <span className="scanner__hint">Aproxime o código de barras…</span>
          </>,
        );
      },

      willClose: () => {
        root?.unmount(); // encerra câmera e limpa memória
      },
    });
  }

  /* ---------------------------------------------------------------
   * Expondo a abertura inicial do scanner
   * ------------------------------------------------------------- */
  return openScannerModal;
}
