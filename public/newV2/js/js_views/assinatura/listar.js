/* eslint-disable no-unused-vars, no-undef */
$(document).ready(function () {

    /* -------------------------------------------------- *
     * 0)  CSRF para TODAS as requisições
     * -------------------------------------------------- */
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
  
    /* -------------------------------------------------- *
     * 1)  Select2 no dropdown de status
     * -------------------------------------------------- */
    $('#pesquisa-status').select2Simple();
  
  
    /* -------------------------------------------------- *
     * 2)  DataTable — uma única instância
     * -------------------------------------------------- */
    const $tbl  = $('#assinaturasTable');
    const table = $.fn.dataTable.isDataTable($tbl)
                 ? $tbl.DataTable()
                 : $tbl.DataTable({
                     language: { url: '/assets/datatables/pt-BR.json' },
                     order:    [],
                     columnDefs:[{ targets:'.nosort', orderable:false }]
                   });
  
    /* -------------------------------------------------- *
     * 3)  Adiciona data-status em cada <tr> (para filtrar)
     * -------------------------------------------------- */
    function aplicaDataStatus () {
      $tbl.find('tbody tr').each(function () {
        const status = $(this).find('td.status .ocultar').text().trim().toLowerCase();
        if (status) $(this).attr('data-status', status);   // assinado | pendente | rascunho | reprovado
      });
    }
    aplicaDataStatus();
  
  
    /* -------------------------------------------------- *
     * 4)  Filtro customizado por status
     *     Valores possíveis no <select>:
     *        '' (nada) | t (todos)
     *        as (Assinado)  → assinado
     *        pe (Pendente)  → pendente
     *        rc (Rascunho)  → rascunho
     *        fr (Reprovado) → reprovado
     *     Se o option já devolve o texto completo (“pendente” …),
     *     o map abaixo simplesmente devolverá o mesmo valor.
     * -------------------------------------------------- */
    const mapSel = { as:'assinado', pe:'pendente', rc:'rascunho', fr:'reprovado', t:'' };
  
    $.fn.dataTable.ext.search.push((settings, data, idx) => {
      if (settings.nTable !== table.table().node()) return true;
  
      let sel = $('#pesquisa-status').val() || '';
      sel = mapSel.hasOwnProperty(sel) ? mapSel[sel] : sel;   // converte código → nome
  
      if (!sel) return true;                                  // “Todos” ou vazio
  
      // 1ª tentativa rápido via atributo data-status
      let actual = $(table.row(idx).node()).attr('data-status');
  
      // fallback (linha recém-chegada por AJAX)
      if (!actual) {
        const txt = $(table.row(idx).node()).find('td:first .ocultar').text().trim().toLowerCase();
        if (/assinado/i.test(txt))       actual = 'assinado';
        else if (/pendente/i.test(txt))  actual = 'pendente';
        else if (/rascunho/i.test(txt))  actual = 'rascunho';
        else if (/reprovado|rejeit/i.test(txt)) actual = 'reprovado';
        else                                   actual = 'other';
      }
  
      return actual === sel;
    });
  
    /* redesenha quando muda o select */
    $('#pesquisa-status')
      .on('change select2:select select2:clear', () => table.draw());
  
  
    /* -------------------------------------------------- *
     * 5)  Exportar XLS (linhas visíveis)
     * -------------------------------------------------- */
    $('.btn-generate-excel').off('click').on('click', e => {
      e.preventDefault();
  
      const headers = table.columns().header().toArray()
        .map(th => `<th>${$(th).text().trim()}</th>`).join('');
  
      let rows = '';
      table.rows({ search:'applied' }).every(function () {
        const cells = $(this.node()).find('td').toArray()
          .map(td => `<td>${$(td).text().trim()}</td>`).join('');
        rows += `<tr>${cells}</tr>`;
      });
  
      const html =
        '<html><head><meta charset="utf-8"/></head><body>' +
        `<table border="1"><thead><tr>${headers}</tr></thead><tbody>${rows}</tbody></table>` +
        '</body></html>';
  
      const blob = new Blob(['\ufeff' + html], { type:'application/vnd.ms-excel;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'assinaturas.xls';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
  
  
    /* -------------------------------------------------- *
     * 6)  (Opcional) Filtro AJAX no backend
     * -------------------------------------------------- */
    $(document).on('change', '#pesquisa-status', function () {
      const statusSrc = $(this).val() || '';
      const status    = mapSel.hasOwnProperty(statusSrc) ? mapSel[statusSrc] : statusSrc;
  
      $.ajax({
        url:    '/assinaturas/filter',
        method: 'GET',
        data:   { status },
        success: function (html) {
          $tbl.find('tbody').html(html);
          aplicaDataStatus();
          table.draw(false);
        },
        error: function (xhr) {
          swal('Erro', 'Não foi possível filtrar as assinaturas.', 'error');
          console.error(xhr.responseText);
        }
      });
    });
  
  
    /* -------------------------------------------------- *
     * 7)  Modal Motivo da rejeição (AJAX)
     * -------------------------------------------------- */
    $(document).on('click', '.show_modal_motivo', function (e) {
      e.preventDefault();
      const id = $(this).data('id');
      if (!id) { swal('Erro', 'ID não informado.', 'error'); return; }
  
      $.ajax({
        url:    '/api/get-motivo-rejeicao',
        method: 'GET',
        data:   { id },
        success: function (resp) {
          const motivo = resp.motivo || 'Motivo não informado.';
          $('#label_motivo').html('Motivo da rejeição');
          $('.descricao_motivo').html(motivo);
          $('#modal_motivo').modal('show');
        },
        error: function (xhr) {
          swal('Erro', 'Não foi possível carregar o motivo da rejeição.', 'error');
          console.error(xhr.responseText);
        }
      });
    });
  
  
    /* -------------------------------------------------- *
     * 8)  Reenviar  → status pendente
     * -------------------------------------------------- */
    $(document).on('click', '.upload', function (e) {
      e.preventDefault();
      const id = $(this).data('id');
      if (!id) { swal('Erro', 'ID não informado.', 'error'); return; }
  
      swal({
        title: 'Deseja prosseguir?',
        text:  'Esta ação atualizará o status para "pendente".',
        type:  'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor:  '#d33',
        confirmButtonText:  'Sim',
        cancelButtonText:   'Não'
      }).then(function () {
        $.ajax({
          url:    '/api/atualizar-status-assinaturas',
          method: 'POST',
          data: {
            ids: [id],
            status: 'pendente',
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (resp) {
            if (resp.success) {
              swal('Sucesso', 'Status atualizado para pendente.', 'success')
                .then(() => location.reload());
            } else {
              swal('Erro', resp.error || 'Não foi possível atualizar o status.', 'error');
            }
          },
          error: function (xhr) {
            swal('Erro', 'Erro ao atualizar o status.', 'error');
            console.error(xhr.responseText);
          }
        });
      }).catch(swal.noop);
    });
  
  });
  