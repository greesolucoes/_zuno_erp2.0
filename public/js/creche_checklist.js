$(function () {
    function addAnexoHandler() {
        $('.btn-file').each(function () {
            let anx_block = $(this).closest('div[name="anexo-block"]');

            $(this).off('click').on('click', function () {
                let input = anx_block.find('input[type="file"]');
                let img = anx_block.find('img.hc__anexo');

                input.trigger('click');

                input.off('change').on('change', function () {
                    let file = this.files[0];
                    if (file) {
                        img.attr('src', URL.createObjectURL(file));
                        anx_block.find('.remove-image-btn').show();
                    }
                });
            });

            if (anx_block.find('img.hc__anexo').attr('src').includes('/imgs/no-image.png') && $('.btn-file').length <= 1) {
                anx_block.find('.remove-image-btn').hide();
            } else {
                anx_block.find('.remove-image-btn').show();
            }
        });

        if ($('.hc__anexo').length >= 4) {
            $('.add-image-btn').hide();
        }
    }
    addAnexoHandler();

    function removeAnexoHandler() {
        $('.remove-image-btn').each(function () {
            $(this).off('click').on('click', function () {
                addAnexoHandler();

                let anx_block = $(this).closest('div[name="anexo-block"]');
                let anx_to_remove_url = anx_block.find('img.hc__anexo').attr('src');
                if (!anx_to_remove_url.includes('/imgs/no-image.png') && !anx_to_remove_url.includes('blob:http')) {
                    let anx_to_remove_field = $('<input>', {
                        type: 'hidden',
                        name: 'anexos_to_remove[]',
                        value: anx_to_remove_url
                    });
                    $('div[name="anexo-container"]').append(anx_to_remove_field);
                }

                if ($('.hc__anexo').length <= 1) {
                    anx_block.find('input[type="file"]').val('');
                    anx_block.find('img.hc__anexo').attr('src', '/imgs/no-image.png');
                    $('.remove-image-btn').hide();
                    return;
                }

                $(this).closest('div[name="anexo-block"]').remove();

                if ($('.hc__anexo').length < 4) {
                    $('.add-image-btn').show();
                }
            });
        });
    }
    removeAnexoHandler();

    $('.add-image-btn').on('click', function () {
        if ($('.hc__anexo').last().attr('src').includes('/imgs/no-image.png')) {
            new swal('Atenção', 'Selecione uma imagem antes para adicionar mais um anexo', 'warning');
            return;
        }

        if ($('.hc__anexo').length >= 4) {
            new swal('Atenção', 'Você só pode inserir até 4 anexos', 'error');
            return;
        }

        let old_anx_block = $('div[name="anexo-block"]').last();
        let clone = old_anx_block.clone();

        clone.find('input[type="file"]').val('');
        clone.find('img.hc__anexo').attr('src', '/imgs/no-image.png');

        old_anx_block.after(clone);

        addAnexoHandler();
        removeAnexoHandler();
    });
});


$('#btn-print').on('click', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Tem certeza de que deseja imprimir?',
        text: "Ao imprimir todas as alterações feitas serão salvas",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, imprimir',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const params = new URLSearchParams(window.location.search);
            const tipo_checklist = params.get("tipo");

            let form = $('#main-form');
            let action = form.attr('action');

            form.attr('action', action + '?imprimir=1' + '&tipo=' + tipo_checklist);

            form.trigger('submit');
        }
    })
});