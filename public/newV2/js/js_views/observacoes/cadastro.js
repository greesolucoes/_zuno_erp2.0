function contaCharObservacoes() {
    var maxLenObs = 254;
    var char = 0;
    $('#observacoes').keyup(function () {
        var len = $(this).val().length;
        if (len > maxLenObs) {
            char = 0;
            $(this).val($(this).val().substring(0, maxLenObs));
        } else {
            char = maxLenObs - len;
        }
        $('p#numChars').text(char + ' ' + l["caracteresRestantes"]);
    });
    char = maxLenObs - $('#observacoes').val().length;
    $('p#numChars').text(char + ' ' + l["caracteresRestantes"]);
}

contaCharObservacoes();