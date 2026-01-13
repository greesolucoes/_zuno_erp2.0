/* eslint-disable no-unused-vars, no-undef */
$(document).ready(function () {

    /* -------------------------------------------------- *
     * 1) CSRF para TODAS as requisições AJAX
     * -------------------------------------------------- */
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
  
    /* -------------------------------------------------- *
     * 2)  DataTable (inicializa uma única vez)
     * -------------------------------------------------- */
    const $tbl  = $('table[data-table="table"]');
    const table = $.fn.dataTable.isDataTable($tbl) ? $tbl.DataTable() : $tbl.DataTable();
  
    /* -------------------------------------------------- *
     * 3)  Auxiliar de data  "dd/mm/aaaa" → Date
     * -------------------------------------------------- */
    const parseDMY = str => {
      const [d, m, y] = str.split('/');
      return new Date(y, m - 1, d);
    };
  
    /* Deixa os campos de data vazios para não filtrar na carga  */
    $('#dataInicial, #dataFinal').val('');
  
    /* -------------------------------------------------- *
     * 4)  Filtro customizado (datas + status) — inclui LOGS
     * -------------------------------------------------- */
    $.fn.dataTable.ext.search.push((settings, data, idx) => {
  
      /* só nossa tabela */
      if (settings.nTable !== table.table().node()) return true;
  
      const de     = $('#dataInicial').val();
      const ate    = $('#dataFinal').val();
      const status = $('#pesquisa-status').val();
  
      console.log(`[Filtro] linha ${idx} – de="${de}" | ate="${ate}" | filtroStatus="${status}"`);
  
      /* --- DATA -------------------------------------------------- */
      if (de) {
        const min  = parseDMY(de);
        const cell = new Date(data[2].split(' ')[0]);          // "yyyy-mm-dd ..."
        if (cell < min) return false;
      }
      if (ate) {
        const max   = parseDMY(ate);
        const cell2 = new Date(data[2].split(' ')[0]);
        if (cell2 > max) return false;
      }
  
      /* --- STATUS ------------------------------------------------ */
      if (status) {
        const actualStatus = $(table.row(idx).node()).attr('data-status')?.trim(); // approved / pending / rejected / outro
        console.log(`  • status real = "${actualStatus}"`);
  
        switch (status) {
          case 'sp': return actualStatus === 'approved';                           // Solicitação aprovada
          case 'sa': return actualStatus === 'pending';                            // Aguardando aprovação
          case 'sr': return actualStatus === 'rejected';                           // Rejeitada
          case 'e' : return !['approved','pending','rejected'].includes(actualStatus); // Erro específico
        }
      }
  
      return true;                                                                 // passa se não filtrar
    });
  
    /* -------------------------------------------------- *
     * 5)  Botão “Pesquisar”
     * -------------------------------------------------- */
    $('#search-table').off('click').on('click', e => {
      e.preventDefault();
      const antes  = table.rows({ filter:'applied' }).count();
      table.draw();
      const depois = table.rows({ filter:'applied' }).count();
      console.log(`[Filtro] linhas antes=${antes}, depois=${depois}`);
    });
  
    /* -------------------------------------------------- *
     * 6)  Modal Tipo de Cadastro
     * -------------------------------------------------- */
    $('#config-tipo_cadastro').off('click').on('click', () => {
      $('.modal-tipo_cadastro').modal('toggle');
    });
  
    /* ===================================================================== *
     *  A PARTIR DAQUI – funções fornecidas originalmente                    *
     *  (aprovar, rejeitar, uploads, etc.) –  **NÃO alteradas**              *
     * ===================================================================== */
  
    // ---------- Notificações (mantido)
    function showNotifications(ns){
      ns.forEach(n=>swal(n.title??'Aviso', n.message??'', n.type??'info'));
    }
  
    // ---------- Rejeitar ---------------------------------------------------
    function showModalRejeitar(){
      $('button.show_modal_reject').unbind('click').click(function(e){
        e.preventDefault();
        $('.modal_rejeitar button.rejeitar')
          .data('id',  $(this).data('id'))
          .data('url', $(this).data('url'));
        $('#motivo').val('');
        $('.modal_rejeitar').modal('toggle');
      });
    }
  
    function acaoRejeitar(){
      $('.modal_rejeitar button.rejeitar').unbind('click').on('click',function(e){
        e.preventDefault();
        const id   = $(this).data('id');
        const url  = $(this).data('url');
        const note = $('#motivo').val().trim();
        if(!note){
          swal("Atenção","Informe o motivo da rejeição.","warning"); return;
        }
        toggleLoading();
        ajaxRequest(true,url,null,'json',{comentario:note},ret=>{
          if(is_empty(ret,1) || ret.status==='error'){
            swal(l['erro'],ret.message||l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],"error");
            toggleLoading(); return;
          }
          swal(l['sucesso!'],ret.message,"success").catch(swal.noop);
          toggleLoading();
          $('.modal_rejeitar').modal('hide');
          table.draw(false);
        });
      });
    }
  
    // ---------- Motivo -----------------------------------------------------
    function showModalMotivo(){
      $('button.show_modal_motivo').unbind('click').click(function(e){
        e.preventDefault();
        $('.descricao_motivo').html('');
        $('.modal_motivo').modal('toggle');
      });
    }
  
    // ---------- Aprovar ----------------------------------------------------
    function acaoAprovar(){
      $('.aprovar').unbind('click').on('click',function(e){
        e.preventDefault();
        const url = $(this).data('url');
        const id  = $(this).data('id');
        const tableDataTable = $(this).closest('.table-exibe').DataTable();
        swal({
          title:l['essaAcaoSeraIrreversivel'],
          text :l['desejaContinuar?'],
          type :'warning',
          showCancelButton:true,
          confirmButtonColor:'#3085d6',
          cancelButtonColor:'#d33',
          confirmButtonText:"Aprovar pedido",
          cancelButtonText:"Desfazer"
        }).then(()=>{
          toggleLoading();
          ajaxRequest(true,url,null,'json',{id},ret=>{
            if(is_empty(ret,1) || ret.status==='error'){
              swal(l['erro'],ret.message||l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],"error");
              toggleLoading(); return;
            }
            swal(l['sucesso!'],ret.message,"success").catch(swal.noop);
            tableDataTable.draw();
            toggleLoading();
          });
        }).catch(swal.noop);
      });
    }
  
    /* -------------------------------------------------- *
     * 12) Exportar XLS
     * -------------------------------------------------- */
    function registrosExcel(){
      $('#gerarRelatorio').off('click').on('click', e => {
        e.preventDefault();
        const ths = table.columns().header().toArray().map(th => '<th>' + $(th).text().trim() + '</th>').join('');
        let trs = '';
        table.rows({ search: 'applied' }).every(function () {
          const tds = $(this.node()).find('td').toArray().map(td => '<td>' + $(td).text().trim() + '</td>').join('');
          trs += '<tr>' + tds + '</tr>';
        });
        const html = '<html><head><meta charset="utf-8"/></head><body><table border="1"><thead><tr>' + ths + '</tr></thead><tbody>' + trs + '</tbody></table></body></html>';
        const blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'solicitacoes.xls';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });
    }
  
    // ---------- Stubs mantidos --------------------------------------------
    function uploadParaAprovacao(){}
    function uploadReg(){}
    function excluirReg(){}
    function pesquisaPersonalizada(){}
    function duplicarReg(){}
    function criaCostumizacoes(){ $("select#pesquisa-status").select2Simple(); }
  
    /* -------------------------------------------------- *
     * 13) Dispara todas as funções
     * -------------------------------------------------- */
    function allFunctionsSolicitacao(){
      showModalRejeitar();
      acaoRejeitar();
      showModalMotivo();
      uploadParaAprovacao();
      acaoAprovar();
      excluirReg();
      uploadReg();
      duplicarReg();
      registrosExcel();
      pesquisaPersonalizada();
      criaCostumizacoes();
    }
    allFunctionsSolicitacao();
  
    /*  Não dispara filtro automaticamente na carga da página */
  });
