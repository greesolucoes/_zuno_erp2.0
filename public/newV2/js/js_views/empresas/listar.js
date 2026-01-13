/* listar.js — agora à prova de execuções duplicadas */

(() => {
    // Previne que o mesmo arquivo rode duas vezes
    if (window.__listarLoaded__) return;
    window.__listarLoaded__ = true;
  
    // tokenCsrf: garante definição única no escopo global
    if (typeof window.tokenCsrf === 'undefined') {
      window.tokenCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
    const tokenCsrf = window.tokenCsrf; // usado apenas localmente abaixo
  
    /* =======================================================
     * Função: acaoDelete (desativa filial)
     * =====================================================*/
    function acaoDelete() {
      console.log("Inicializando função acaoDelete");
      $('.delete').off('click').on('click', function (e) {
        e.preventDefault();
        console.log("Evento de desativação disparado");
  
        const obj = $(this);
        const url = obj.data('url');
        const id = obj.data('id');
        console.log("Dados obtidos:", { id, url });
  
        const tableDataTable = obj.parents('.table-exibe').DataTable();
  
        swal({
          title: l["desativarEmpresas?"],
          text: l["casoAEmpresaSejaDesativada,TodasAsFiliaisCujasQuaisElaTeriaVinculoSerãoDesativadas,DesejaContinuar?"],
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: l["desativar!"],
          cancelButtonText: l["cancelar!"]
        }).then(() => {
          console.log("Confirmação de desativação recebida");
          toggleLoading();
  
          ajaxRequest(true, url, null, 'json', {
            id,
            status: 'inactive',
            _method: 'PATCH',
            _token: tokenCsrf
          }, ret => {
            console.log("Resposta do AJAX para desativação:", ret);
            toggleLoading();
  
            if (ret.success) {
              swal(l["empresaDesativada"], l["aEmpresaFoiDesativadaEPoderáSerRecuperadaMaisTarde!"], "success");
  
              const $row = obj.parents('tr');
              $row.find('td.status .ocultar').text(l["empresaDesativada"]);
              $row.find('td.status i')
                .removeClass('circle-status-white')
                .addClass('circle-status-red')
                .attr('title', l["empresaDesativada"]);
  
              obj.parents('td').html(
                `<button href="#" data-id="${id}" data-url="${url}" class="btn btn-primary btn-sm mudaStatus" title="Ativar registro">${isOldLayout ? '<i class="fa fa-check"></i>' : '<span data-icon="ic:outline-check" class="iconify"></span>'}</button>`
              );
              acaoAtivar();
  
              if (tableDataTable) tableDataTable.draw();
            } else {
              console.error("Erro na resposta AJAX:", ret.message);
              swal(l["erro"], l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"], "error");
            }
          });
        }).catch(error => {
          console.error("Erro no swal (desativação):", error);
          swal.noop();
        });
      });
    }
  
    /* =======================================================
     * Função: acaoAtivar (ativa filial)
     * =====================================================*/
    function acaoAtivar() {
      console.log("Inicializando função acaoAtivar");
      $('.mudaStatus').off('click').on('click', function (e) {
        e.preventDefault();
        console.log("Evento de ativação disparado");
  
        const obj = $(this);
        const url = obj.data('url');
        const id = obj.data('id');
        console.log("Dados obtidos:", { id, url });
  
        const tableDataTable = obj.parents('.table-exibe').DataTable();
  
        swal({
          title: l["ativarEmpresa?"],
          text: l["temCertezaDeQueDesejaAtivarEssaEmpresa!"],
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: l["ativar!"],
          cancelButtonText: l["cancelar!"]
        }).then(() => {
          console.log("Confirmação de ativação recebida");
          toggleLoading();
  
          ajaxRequest(true, url, null, 'json', {
            id,
            status: 'active',
            _method: 'PATCH',
            _token: tokenCsrf
          }, ret => {
            console.log("Resposta do AJAX para ativação:", ret);
            toggleLoading();
  
            if (ret.success) {
              swal(l["empresaAtivada"], l["aEmpresaFoiAtivadaComSucesso!"], "success");
  
              const $row = obj.parents('tr');
              $row.find('td.status .ocultar').text(l["empresaAtivada"]);
              $row.find('td.status i')
                .removeClass('circle-status-red')
                .addClass('circle-status-white')
                .attr('title', l["empresaAtivada"]);
  
              obj.remove();
              if (tableDataTable) tableDataTable.draw();
            } else {
              console.error("Erro na resposta AJAX:", ret.message);
              swal(l["erro"], l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"], "error");
            }
          });
        }).catch(error => {
          console.error("Erro no swal (ativação):", error);
          swal.noop();
        });
      });
    }
  
    // Inicializa funções
    acaoDelete();
    acaoAtivar();
  })();
  