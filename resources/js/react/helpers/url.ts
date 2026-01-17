export function construirUrlWhatsApp(telefone: string, id: number) {
    const telefoneFormatado = telefone.replace(/[()\-\s]/g, '');
    const url = `https://api.whatsapp.com/send?phone=${telefoneFormatado}&text=${encodeURIComponent('Seu orçamento está pronto, clique no link para baixar: ' + `${window.location.origin}/orcamentos/imprimir/${id}`)}`;
    return url;
}
