import { TiposPagamento } from '../types';

export const reorganizarPorValor = (obj: TiposPagamento): TiposPagamento => {
    const entries = Object.entries(obj);
    entries.sort((a, b) => a[1].localeCompare(b[1]));
    const sortedObj: TiposPagamento = {};
    for (const [key, value] of entries) {
        sortedObj[key] = value;
    }
    return sortedObj;
};

export const ordenarPorValor = (
    array: [string, string][],
): [string, string][] => {
    return array.sort((a, b) => a[1].localeCompare(b[1]));
};
