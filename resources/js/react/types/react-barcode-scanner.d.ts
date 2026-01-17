/* Tipagem mínima para satisfazer o TypeScript ―
 * cobre exatamente o que usamos no PDV.
 */

declare module 'react-barcode-scanner' {
  import * as React from 'react';

  export interface DetectedBarcode {
    rawValue: string;
    format: string;
    boundingBox?: DOMRectReadOnly;
    cornerPoints?: Array<{ x: number; y: number }>;
  }

  export interface BarcodeScannerProps {
    onCapture: (barcodes: DetectedBarcode[]) => void;
    options?: {
      formats?: string[];
      delay?: number;          // ms entre leituras (default 1000)
    };
    paused?: boolean;
  }

  export const BarcodeScanner: React.FC<BarcodeScannerProps>;
}
