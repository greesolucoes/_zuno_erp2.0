$(document).ready(function () {
    // Configuração global do CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ==============================
    // 1) Mostrar/ocultar campos do modal
    // ==============================
    $('input[name="opt-tipo-importacao-conciliacao"]').on('change', function(){
        var tipo = this.value;
        console.log('[radio change] tipoSelecionado =', tipo);

        if (tipo === 'intervalo') {
            $('.intervalo').removeClass('ocultar');
            $('.unico').addClass('ocultar');
            console.log('→ exibindo intervalo, escondendo único');
        } else {
            $('.unico').removeClass('ocultar');
            $('.intervalo').addClass('ocultar');
            console.log('→ exibindo único, escondendo intervalo');
        }
    });

    // dispara uma vez para aplicar o estado inicial (checked no HTML)
    $('input[name="opt-tipo-importacao-conciliacao"]:checked').trigger('change');

    // ==============================
    // 2) Evento de clique em "Buscar"
    // ==============================
    $('#salvar-dias-para-importar').on('click', function(e) {
        e.preventDefault();
        console.log('--- iniciar busca de assinaturas manualmente ---');

        // Determina qual opção está selecionada
        var tipoBusca = $('input[name="opt-tipo-importacao-conciliacao"]:checked').val();
        console.log('tipoBusca =', tipoBusca);

        var ajaxData = {};

        if (tipoBusca === 'intervalo') {
            var dataDe   = $('#data_intervalo_de_importar-conciliacao_de').val();
            var dataPara = $('#data_intervalo_para_importar-conciliacao_ate').val();
            console.log('datas preenchidas | de =', dataDe, '| para =', dataPara);

            if (!dataDe || !dataPara) {
                console.warn('datas não foram preenchidas');
                swal("Atenção", "Por favor, preencha as datas de início e fim.", "warning");
                return;
            }

            ajaxData = {
                tipo:    'intervalo',
                dataDe:  dataDe,
                dataPara: dataPara
            };
        } else {
            var dataUnica = $('#data_unica_importar-conciliacao').val();
            console.log('data única preenchida =', dataUnica);

            if (!dataUnica) {
                console.warn('data única não preenchida');
                swal("Atenção", "Por favor, preencha a data única.", "warning");
                return;
            }

            ajaxData = {
                tipo:     'unico',
                dataUnica: dataUnica
            };
            swal("Busca por data única", dataUnica, "info");
        }

        console.log('dados para AJAX →', ajaxData);

        // Requisição AJAX
        $.ajax({
            url: '/api/usuarios-fechamentos',
            method: 'GET',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                console.log('AJAX sucesso, response =', response);

                // destrói DataTable existente
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#modal-resultados .table-exibe')) {
                    console.log('destruindo DataTable existente');
                    $('#modal-resultados .table-exibe').DataTable().destroy();
                }

                // insere HTML ou mensagem de nenhum resultado
                if (response.total > 0 && response.html) {
                    console.log('há resultados (total =', response.total, ')');
                    $('#lista-usuarios-fechamento').html(response.html);
                    $('#nenhum-resultado').hide();

                    if ($.fn.DataTable) {
                        console.log('inicializando DataTable');
                        $('#modal-resultados .table-exibe').DataTable({
                            colReorder:   true,
                            pageLength:   10,
                            lengthChange: true,
                            searching:    true,
                            ordering:     true,
                            language: {
                                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
                            }
                        });
                    }
                } else {
                    console.log('nenhum resultado encontrado');
                    $('#lista-usuarios-fechamento').empty();
                    $('#nenhum-resultado').show();
                }

                // lógica de seleção de linhas
                var primeiraSelecao = false;
                $('#modal-resultados .table-exibe tbody')
                    .off('click')
                    .on('click', 'tr', function () {
                        var $linha = $(this);
                        var todas  = $('#modal-resultados .table-exibe tbody tr');

                        console.log('clicou na linha:', $linha.data());
                        if (!primeiraSelecao && !$linha.hasClass("selected")) {
                            primeiraSelecao = true;
                            console.log('primeira seleção → perguntando se seleciona todas');
                            swal({
                                title: "Selecionar todas?",
                                text: "Deseja selecionar todas as linhas da tabela?",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: "Sim",
                                cancelButtonText: "Não"
                            }).then(function () {
                                console.log("Usuário clicou em Sim → selecionando todas");
                                todas.addClass("selected");
                            }).catch(function () {
                                console.log("Usuário clicou em Não → selecionando apenas esta");
                                $linha.toggleClass("selected");
                            });
                        } else {
                            $linha.toggleClass("selected");
                            console.log("toggleClass('selected') na linha");
                        }
                    });

                // troca de modais
                console.log('fechando modal-importar e abrindo modal-resultados');
                $('#modal-importar-dias-template-conciliacao').modal('hide');
                $('#modal-resultados').modal('show');
            },
            error: function(xhr, status, error) {
                console.error("Erro na requisição:", status, error);
                swal("Erro", "Ocorreu um erro ao buscar os fechamentos.", "error");
            }
        });
    });

    // ==============================
    // 3) Evento do botão "Salvar" no modal de resultados
    // ==============================
    $('#btn-salvar-status').on('click', function() {
        console.log('clicou em Salvar status');
        var selecionadas = $('#modal-resultados .table-exibe tbody tr.selected');
        console.log('linhas selecionadas → count =', selecionadas.length);

        if (selecionadas.length === 0) {
            swal("Atenção", "Por favor, selecione pelo menos uma linha para salvar.", "warning");
            return;
        }

        var idsParaAtualizar = [];
        selecionadas.each(function(){
            var id = $(this).data('assinatura-id');
            console.log('pegando data-assinatura-id =', id);
            idsParaAtualizar.push(id);
        });

        console.log('enviando IDs para atualização →', idsParaAtualizar);
        $.ajax({
            url: '/api/atualizar-status-assinaturas',
            method: 'POST',
            data: {
                ids:    idsParaAtualizar,
                status: 'pendente'
            },
            success: function(response) {
                console.log('update status response =', response);
                if (response.success) {
                    swal("Sucesso", "Status atualizado com sucesso!", "success");
                    $('#modal-resultados').modal('hide');
                } else {
                    swal("Erro", "Ocorreu um erro ao atualizar os registros.", "error");
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição de update:', status, error);
                swal("Erro", "Erro na requisição.", "error");
            }
        });
    });
});
