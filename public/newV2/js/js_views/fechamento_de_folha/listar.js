/* eslint-disable no-unused-vars, no-undef */
// ---------------------------------------------------------------------------
//  SCRIPT – DataTable + filtro instantâneo por status + ações + export XLS
// ---------------------------------------------------------------------------
$(document).ready(function () {

    /* -------------------------------------------------- *
     * 1)  CSRF em TODAS as requisições AJAX
     * -------------------------------------------------- */
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
  
    /* -------------------------------------------------- *
     * 2)  DataTable – uma única instância
     * -------------------------------------------------- */
    const $tbl  = $('.table-exibe');
    const table = $.fn.dataTable.isDataTable($tbl)
      ? $tbl.DataTable()
      : $tbl.DataTable();
  
    /* -------------------------------------------------- *
     * 3)  Se o Blade ainda não adiciona data-status, faz agora
     *     (evita ler o HTML toda vez no filtro)
     * -------------------------------------------------- */
    $tbl.find('tbody tr').each(function () {
      let status = $(this).find('td.status .ocultar').text().trim().toLowerCase();
      if (status) {
        $(this).attr('data-status', status);  // 'aprovado' | 'pendente' | 'reprovado'
      }
    });
  
    /* -------------------------------------------------- *
     * 4)  Filtro customizado – Status portal
     * -------------------------------------------------- */
    $.fn.dataTable.ext.search.push((settings, data, idx) => {
      if (settings.nTable !== table.table().node()) return true;
  
      const sel = $('#pesquisa-status').val();  // t | a | aa | fr | null
      if (!sel || sel === 't') return true;     // sem filtro ou 'Todos'
  
      // 1ª tentativa: atributo data-status
      let actual = $(table.row(idx).node()).attr('data-status');
  
      // fallback: texto oculto
      if (!actual) {
        const txt = $(table.row(idx).node()).find('td:first .ocultar').text().trim();
        if (/aprovado/i.test(txt))           actual = 'aprovado';
        else if (/pendente|assinatura/i.test(txt)) actual = 'pendente';
        else if (/reprovado|rejeit/i.test(txt))    actual = 'reprovado';
        else                                      actual = 'other';
      }
  
      // compara com o selecionado
      switch (sel) {
        case 'a' :  return actual === 'aprovado';
        case 'aa':  return actual === 'pendente';
        case 'fr':  return actual === 'reprovado';
      }
      return true;
    });
  
    /* -------------------------------------------------- *
     * 5)  Redesenha tabela instantaneamente
     * -------------------------------------------------- */
    $('#pesquisa-status')
      .on('change select2:select select2:clear', () => table.draw());
  
    /* -------------------------------------------------- *
     * 6)  Inicializa Select2 e ações dos botões
     * -------------------------------------------------- */
    criaCostumizacoes();
    acoesBotoes();
  
    /* -------------------------------------------------- *
     * 7)  Exportar XLS (registros visíveis)
     * -------------------------------------------------- */
    $('.btn-generate-excel').off('click').on('click', e => {
      e.preventDefault();
  
      // monta cabeçalhos
      const headers = table.columns().header().toArray()
        .map(th => `<th>${$(th).text().trim()}</th>`)
        .join('');
  
      // monta linhas filtradas
      let rows = '';
      table.rows({ search: 'applied' }).every(function () {
        const cells = $(this.node()).find('td').toArray()
          .map(td => `<td>${$(td).text().trim()}</td>`)
          .join('');
        rows += `<tr>${cells}</tr>`;
      });
  
      // armazena HTML da planilha
      const html =
        '<html><head><meta charset="utf-8"/></head><body>' +
        `<table border="1"><thead><tr>${headers}</tr></thead><tbody>${rows}</tbody></table>` +
        '</body></html>';
  
      // gera e dispara download
      const blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'fechamentos_de_folha.xls';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
  
  }); // fecha document.ready
  
  
  /* ======================================================================== *
   *  AÇÕES DOS BOTÕES (aprovar, reenviar, desativar, rejeitar, etc.)
   * ====================================================================== */
  function acoesBotoes() {
  
    /* ---------- Ação simples (POST via ajaxRequest) ---------- */
    const __acaoPadrao = function ($btn, payload) {
      const id  = $btn.data('id');
      const url = $btn.data('url');
      if (is_empty(id,1) || is_empty(url,1)) return;
  
      payload = payload || {};
      payload.id = id;
      const dt  = $btn.parents('.table-exibe').DataTable();
  
      swal({
        title: l["desejaContinuar?"],
        type:  "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor:  '#d33',
        confirmButtonText:  l["continuar!"],
        cancelButtonText:   l["fechar!"]
      }).then(() => {
        toggleLoading();
        $.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        ajaxRequest(true, url, null, 'text', payload, ret => {
          try {
            ret = JSON.parse(ret);
            swal(ret.titulo, ret.text, ret.class).catch(swal.noop);
            dt.draw(false);
          } catch {
            swal(l["erro!"], l["tempoDeRespostaDoServidorEsgotado!"], "error").catch(swal.noop);
          } finally {
            toggleLoading();
          }
        });
      }).catch(swal.noop);
    };
  
    /* ---------- Ação que exige motivo (rejeitar) ------------- */
    const __acaoWithDescription = function ($btn) {
      $('.modal_reject #label_reject').html($btn.attr('title'));
      $('#motivo_text').val('');
      $('.modal_reject').modal('toggle');
  
      $('.modal_reject button.reject')
        .off('click')
        .on('click', function () {
          const motivo = $('#motivo_text').val().trim();
          const id     = $btn.data('id');
          const url    = $btn.data('url');
          if (is_empty(id,1) || is_empty(url,1)) return;
  
          const payload = { id, motivo };
          const dt      = $btn.parents('.table-exibe').DataTable();
  
          swal({
            title: l["desejaContinuar?"],
            type:  "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor:  '#d33',
            confirmButtonText:  l["continuar!"],
            cancelButtonText:   l["fechar!"]
          }).then(() => {
            toggleLoading();
            $.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            ajaxRequest(true, url, null, 'text', payload, ret => {
              try {
                ret = JSON.parse(ret);
                swal(ret.titulo, ret.text, ret.class).catch(swal.noop);
                dt.draw(false);
                $('.modal_reject').modal('toggle');
              } catch {
                swal(l["erro!"], l["tempoDeRespostaDoServidorEsgotado!"], "error").catch(swal.noop);
              } finally {
                toggleLoading();
              }
            });
          }).catch(swal.noop);
        });
    };
  
    /* ---------- Delegação de eventos dentro da tabela -------- */
    const $grid = $('.table-exibe');
    $grid
      .off('click', '.aprovar').on('click', '.aprovar',   function(){ __acaoPadrao($(this)); })
      .off('click', '.upload').on('click',  '.upload',    function(){ __acaoPadrao($(this)); })
      .off('click', '.desativar').on('click','.desativar', function(){ __acaoPadrao($(this)); })
      .off('click', '.show_modal_reject').on('click','.show_modal_reject', function(){ __acaoWithDescription($(this)); })
      .off('click', '.show_modal_motivo').on('click', '.show_modal_motivo', function(){
        const titulo    = $(this).attr('title');
        const descricao = $(this).parents('td').find('.descricao_rejeicao').html();
        $('#label_motivo').html(titulo);
        $('.descricao_motivo').html(descricao);
        $('.modal_motivo').modal('toggle');
      });
  }
  
  
  /* ======================================================================== *
   *  CUSTOMIZAÇÕES (Select2 no filtro de status)
   * ====================================================================== */
  function criaCostumizacoes() {
    $('#pesquisa-status').select2Simple();
  }
  