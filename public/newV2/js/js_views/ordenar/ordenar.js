function ativaOrdenacao() {
    $("#ordenacao").sortable({
        items : '.ord_item'
    });
    $("#ordenacao").disableSelection();
}

ativaOrdenacao();