export function formatCNPJ(cnpj: string) {
    cnpj = cnpj.replace(/[^\d]/g, '');
    return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
}

export const dateToStr = (strDate: string) => {
    if (strDate === '') return '';
    const [year, month, day] = strDate.split('-');
    return `${day}/${month}/${year}`;
};
