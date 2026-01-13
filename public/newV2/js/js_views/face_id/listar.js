/**
 * Created by vitor on 26/08/2017.
 * Adaptado para o Face ID – apenas exclusão.
 */

// Definindo o objeto de traduções
var l = {
    "deletarFace?": "Deseja deletar a face?",
    "deletar!": "Deletar!",
    "cancelar!": "Cancelar!",
    "faceDeletada": "Face deletada",
    "aFaceFoiDeletadaComSucesso!": "A face foi deletada com sucesso!",
    "erro": "Erro",
    "éPossívelQueTenhaOcorridoUmErro,PorFavor,EntreEmContatoComOSuporte": "É possível que tenha ocorrido um erro. Por favor, entre em contato com o suporte."
  };
  
  function acaoDelete() {
    $('.delete').unbind('click');
    $('.delete').on("click", function (e) {
      e.preventDefault();
  
      var obj = $(this);
      var faceUser = $(this).data('faceuser');
      var textDeletar = $('.datas_views').data('lang_deletar');
      var url = "/face/requisicaoDelete/" + faceUser; // Rota que espera DELETE
  
      swal({
        title: "Deseja deletar a face?",
        text: textDeletar,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Deletar!",
        cancelButtonText: "Cancelar!"
      }).then(function () {
        // Exemplo sem toggleLoading(), mas você pode manter se quiser
        $.ajax({
          url: url,
          type: 'DELETE', // <-- IMPORTANTE
          // Se precisar mandar algum dado extra (ou CSRF), inclua aqui:
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (ret) {
            // Se veio sucesso do controller
            swal("Face deletada", "A face foi deletada com sucesso!", "success");
            // Remover da tabela DataTable
            var tableDataTable = obj.parents('.table-exibe').DataTable();
            tableDataTable.row(obj.parents('tr')).remove().draw();
          },
          error: function () {
            swal("Erro", "Possível erro ao deletar, contate o suporte!", "error");
          }
        });
      })
      .catch(swal.noop);
    });
  }
  
  $(document).ready(function() {
    acaoDelete();
  });
  
  