/* eslint-disable no-unused-vars, no-undef */
// ---------------------------------------------------------------------------
//  LISTAR SOLICITAÇÃO – script completo com correções de delegação de eventos
// ---------------------------------------------------------------------------

$(document).ready(function () {

  /* -------------------------------------------------- *
   * 1)  Envia CSRF em TODAS as requisições AJAX
   * -------------------------------------------------- */
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  /* -------------------------------------------------- *
   * 2)  DataTable – garante 1 única instância
   * -------------------------------------------------- */
  const $tbl  = $('table[data-table="table"]');
  const table = $.fn.dataTable.isDataTable($tbl) ? $tbl.DataTable() : $tbl.DataTable();

  /* -------------------------------------------------- *
   * 3)  Helper: “dd/mm/aaaa” → Date
   * -------------------------------------------------- */
  const parseDMY = str => {
    if (!str) return null;
    const [d, m, y] = str.split('/');
    return new Date(y, m - 1, d);
  };

  /* limpa datas para não filtrar na carga */
  $('#dataInicial, #dataFinal').val('');

  /* -------------------------------------------------- *
   * 4)  Filtro customizado (data + status)
   * -------------------------------------------------- */
  $.fn.dataTable.ext.search.push((settings, data, idx) => {

    /* aplica só para esta tabela */
    if (settings.nTable !== table.table().node()) return true;

    const de     = $('#dataInicial').val();
    const ate    = $('#dataFinal').val();
    const status = $('#pesquisa-status').val();

    /* --- DATA ------------------------------------------------ */
    if (de) {
      const min  = parseDMY(de);
      const cell = new Date(data[2].split(' ')[0]);     // “yyyy-mm-dd …”
      if (cell < min)  return false;
    }
    if (ate) {
      const max   = parseDMY(ate);
      const cell2 = new Date(data[2].split(' ')[0]);
      if (cell2 > max) return false;
    }

    /* --- STATUS ---------------------------------------------- */
    if (status) {
      /* 1ª tentativa: atributo data-status (melhor desempenho)  */
      let actualStatus = $(table.row(idx).node()).attr('data-status');
      /* fallback: texto escondido no primeiro <td>               */
      if (!actualStatus) {
        const text = $(table.row(idx).node()).find('td:first .ocultar').text().trim();
        if (/aprovado/i.test(text))      actualStatus = 'approved';
        else if (/pendente/i.test(text)) actualStatus = 'pending';
        else if (/rejeitado/i.test(text))actualStatus = 'rejected';
        else                             actualStatus = 'other';
      }

      switch (status) {
        case 'sp': return actualStatus === 'approved';                            // Solicitação aprovada
        case 'sa': return actualStatus === 'pending';                             // Aguardando aprovação
        case 'sr': return actualStatus === 'rejected';                            // Rejeitada
        case 'e' : return !['approved','pending','rejected'].includes(actualStatus); // Erro específico
      }
    }

    return true;  // passa quando não cai em nenhum filtro
  });

  /* -------------------------------------------------- *
   * 5)  Botão “Pesquisar”
   * -------------------------------------------------- */
  $('#search-table').off('click').on('click', e => {
    e.preventDefault();
    table.draw();
  });

  /* -------------------------------------------------- *
   * 6)  Exportar XLS (registros visíveis)
   * -------------------------------------------------- */
  $('#gerarRelatorio').off('click').on('click', e => {
    e.preventDefault();

    const headers = table.columns().header().toArray()
      .map(th => '<th>' + $(th).text().trim() + '</th>')
      .join('');

    let rows = '';
    table.rows({ search: 'applied' }).every(function () {
      const tds = $(this.node()).find('td').toArray()
        .map(td => '<td>' + $(td).text().trim() + '</td>')
        .join('');
      rows += '<tr>' + tds + '</tr>';
    });

    const html =
      '<html><head><meta charset="utf-8"/></head><body>' +
      '<table border="1"><thead><tr>' + headers + '</tr></thead>' +
      '<tbody>' + rows + '</tbody></table></body></html>';

    const blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'solicitacoes.xls';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  });

  /* -------------------------------------------------- *
   * 7)  Modal “Tipo de cadastro”
   * -------------------------------------------------- */
  $('#config-tipo_cadastro').off('click').on('click', () => {
    $('.modal-tipo_cadastro').modal('toggle');
  });

  // ===================================================================== //
  //  Delegação de eventos – garante que cliques funcionem após redraw      //
  // ===================================================================== //

  // Botão “Aprovar” dentro da tabela
  $tbl.on('click', '.aprovar', handleAprovar);

  // Botão que abre o modal de rejeição
  $tbl.on('click', '.show_modal_reject', handleShowRejeitar);

  // Botão que mostra motivo (caso exista)
  $tbl.on('click', '.show_modal_motivo', handleShowMotivo);

  // Botão de exclusão (stub)
  $tbl.on('click', '.excluirReg', handleExcluirReg);

  // Confirmação de rejeição (dentro do modal)
  $('.modal_rejeitar').on('click', 'button.rejeitar', handleRejeitar);

  // -------------------------------------------------- //
  //  FUNÇÕES                                           //
  // -------------------------------------------------- //

  /* ------------ Notificações genéricas -------------- */
  function showNotifications(ns){ ns.forEach(n=>swal(n.title??'Aviso', n.message??'', n.type??'info')); }

  /* ------------ Modal Rejeitar  (abrir) ------------- */
  function handleShowRejeitar(e){
    e.preventDefault();
    const $btn = $(e.currentTarget);
    $('.modal_rejeitar button.rejeitar')
      .data('id',  $btn.data('id'))
      .data('url', $btn.data('url'));
    $('#motivo').val('');
    $('.modal_rejeitar').modal('toggle');
  }

  /* ------------ Rejeitar  (confirmar) --------------- */
  function handleRejeitar(e){
    e.preventDefault();
    const $btn = $(e.currentTarget);
    const id   = $btn.data('id');
    const url  = $btn.data('url');
    const note = $('#motivo').val().trim();

    if(!note){
      swal('Atenção','Informe o motivo da rejeição.','warning');
      return;
    }

    toggleLoading();
    ajaxRequest(true,url,null,'json',{comentario:note},ret=>{
      if(is_empty(ret,1) || ret.status==='error'){
        swal(l['erro'],ret.message||l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],'error');
        toggleLoading();
        return;
      }
      swal(l['sucesso!'],ret.message,'success').catch(swal.noop);
      toggleLoading();
      $('.modal_rejeitar').modal('hide');
      table.draw(false);
    });
  }

  /* ------------ Aprovar ----------------------------- */
  function handleAprovar(e){
    e.preventDefault();
    const $btn = $(e.currentTarget);
    const url  = $btn.data('url');
    const id   = $btn.data('id');

    swal({
      title: l['essaAcaoSeraIrreversivel'],
      text:  l['desejaContinuar?'],
      type:  'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor:  '#d33',
      confirmButtonText:  'Aprovar pedido',
      cancelButtonText:   'Desfazer'
    }).then(()=>{
      toggleLoading();
      ajaxRequest(true,url,null,'json',{id},ret=>{
        if(is_empty(ret,1) || ret.status==='error'){
          swal(l['erro'],ret.message||l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],'error');
          toggleLoading();
          return;
        }
        swal(l['sucesso!'],ret.message,'success').catch(swal.noop);
        table.draw(false);
        toggleLoading();
      });
    }).catch(swal.noop);
  }

  /* ------------ Motivo (visualizar) ----------------- */
  function handleShowMotivo(e){
    e.preventDefault();
    // Aqui você pode preencher .descricao_motivo via AJAX se precisar
    $('.descricao_motivo').text('Carregando...');
    $('.modal_motivo').modal('toggle');
  }

  /* ------------ Excluir (stub) ---------------------- */
  function handleExcluirReg(e){
    e.preventDefault();
    swal('Aviso','Funcionalidade de exclusão ainda não implementada.','info');
  }

  /* ------------ Stubs preservados ------------------- */
  function uploadParaAprovacao(){}
  function uploadReg(){}
  function pesquisaPersonalizada(){}
  function duplicarReg(){}
  function criaCostumizacoes(){ $('select#pesquisa-status').select2Simple(); }

  /* -------------------------------------------------- *
   * 8)  Inicializa demais rotinas isoladas
   * -------------------------------------------------- */
  criaCostumizacoes();

});
