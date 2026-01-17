import { useEffect, useRef } from 'react';
import { Item } from '../types';
import { fetchProductByBarcode } from '../services/productService';

interface UseBarcodeProps {
    empresa_id: number;
    usuario_id: number;
    filial_id?: number | string | null;
    handleAddItem: (item: Item) => Promise<void>;
}

export default function useBarcode({
    empresa_id,
    usuario_id,
    filial_id,
    handleAddItem,
}: UseBarcodeProps) {
    const barcodeRef = useRef('');
    const lastInputTimeRef = useRef(Date.now());
    const queueRef = useRef<string[]>([]);
    const isProcessingRef = useRef(false);
    const barcodeTimeout = 70;
    const submitKeys = useRef(
        new Set([
            'Enter',
            'Tab',
            'ArrowRight',
            'ArrowDown',
            'ArrowLeft',
            'ArrowUp',
        ]),
    );

    useEffect(() => {
        const dispatchBarcodeEvent = (code: string) => {
            if (typeof window !== 'undefined') {
                window.dispatchEvent(
                    new CustomEvent('pdv:barcode-scanned', {
                        detail: { barcode: code },
                    }),
                );
            }
        };

        const processQueue = async (): Promise<void> => {
            if (isProcessingRef.current) return;

            const nextBarcode = queueRef.current.shift();
            if (!nextBarcode) return;

            isProcessingRef.current = true;
            try {
                await fetchProductByBarcode(
                    nextBarcode,
                    empresa_id,
                    usuario_id,
                    filial_id,
                    handleAddItem,
                );
            } catch (error) {
                console.error('Erro ao processar código de barras:', error);
            } finally {
                dispatchBarcodeEvent(nextBarcode);
                isProcessingRef.current = false;
                if (queueRef.current.length > 0) {
                    void processQueue();
                }
            }
        };

        const handleKeyDown = (event: KeyboardEvent) => {
            const currentTime = Date.now();
            const timeDiff = currentTime - lastInputTimeRef.current;

            if (timeDiff > barcodeTimeout) {
                barcodeRef.current = '';
            }

            if (event.key.length === 1) {
                barcodeRef.current += event.key;
            }

            lastInputTimeRef.current = currentTime;

            if (!submitKeys.current.has(event.key)) {
                return;
            }

            const capturedBarcode = barcodeRef.current.trim();
            barcodeRef.current = '';

            if (capturedBarcode.length < 8) {
                return;
            }

            queueRef.current.push(capturedBarcode);
            void processQueue();
        };

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [empresa_id, usuario_id, filial_id, handleAddItem]);
}
