export function formatToCurrency(value: string | number): string {
    const numberValue =
        typeof value === 'string' ? parseFloat(value || '0') : value || 0;

    return numberValue.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
}

export function parseCurrencyToFloat(value: string) {
    value = value.replace('R$', '').trim();
    value = value.replace(/\./g, '');
    value = value.replace(',', '.');
    return parseFloat(value);
}

export function convertBrazilianCurrencyToFloat(value: string): number {
    const sanitizedValue = value
        .replace(/[R$\s]/g, '')
        .replace(',', '.');
    const floatValue = parseFloat(sanitizedValue);
    return isNaN(floatValue) ? 0 : floatValue;
}
