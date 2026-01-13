/* global $, Swal, toastr, tinymce, path_url */
(function () {
  class ProdutoFormsPage {
    constructor(config) {
      this.config = config || {};
      this.abortController = null;
      this.combinacoes = Array.isArray(this.config.combinacoes) ? [...this.config.combinacoes] : [];
      this.debugCategoria = Boolean(this.config?.debugCategoria);
      this.categoriaInitAttempts = 0;
      this.elements = {
        inpSku: document.getElementById('inp-sku'),
        inpCodigo: document.getElementById('inp-codigo'),
        inpEstoqueInicial: document.getElementById('inp-estoque_inicial'),
        codigoVariacao: document.getElementById('codigo_variacao'),
        codigoBarras: document.getElementById('codigo_barras'),
        btnAction: document.querySelector('.btn-action'),
        btnFinalizar: document.getElementById('finalizar'),
        formProduto: document.getElementById('form-produto'),
      };
    }

    log(msg, type, extra) {
      if (!this.debugCategoria) return;
      const prefix = '[Produtos/Categoria] ';
      try {
        if (typeof consoleSystem === 'function') {
          consoleSystem(prefix + msg, type || 'debug');
        } else {
          // fallback
          // eslint-disable-next-line no-console
          console[type === 'error' ? 'error' : type === 'warning' ? 'warn' : type === 'info' ? 'info' : 'log'](
            prefix + msg,
          );
        }
        if (typeof extra !== 'undefined') {
          // eslint-disable-next-line no-console
          console.log(prefix + 'extra:', extra);
        }
      } catch (e) {
        // eslint-disable-next-line no-console
        console.log(prefix + msg);
      }
    }

    init() {
      if (!this.elements.formProduto) return;

      this.abortController = new AbortController();
      window.addEventListener('beforeunload', () => this.abortController?.abort());

      this.initCodigoBarrasInicial();
      this.initPreencherSku();
      this.initCodigoInicial();
      this.initTinyMce();
      this.initSkuHandlers();
      this.initCfopSync();
      this.initCombinacoes();
      this.initVariacaoSync();
      this.initFinalizar();
      this.initImagemPreview();
      this.bindLegacyGlobals();
    }

    initCategoriaSelect2() {
      this.log('initCategoriaSelect2() start', 'info');
      this.log('Config recebida', 'debug', {
        categoriaSelectUrl: this.config.categoriaSelectUrl,
        labelSelectCategoria: this.config.labelSelectCategoria,
      });

      this.log('Dependências', 'debug', {
        hasJquery: typeof window.$ !== 'undefined',
        hasSelect2: typeof window.$ !== 'undefined' ? typeof window.$.fn?.select2 !== 'undefined' : false,
        hasSelect2AjaxTransport: typeof window.$ !== 'undefined',
      });

      if (typeof window.$ === 'undefined') {
        this.log('jQuery ($) não está carregado neste momento. Abortando initCategoriaSelect2().', 'error');
        return;
      }
      if (typeof window.$.fn?.select2 === 'undefined') {
        this.log('Select2 ($.fn.select2) não está carregado. Abortando initCategoriaSelect2().', 'error');
        return;
      }

      let $categoria = $('#inp-categoria_id');
      if (!$categoria.length) $categoria = $('#categoria_id');
      const byIdLen = $categoria.length;
      const byNameLen = $('select[name="categoria_id"]').length;
      const anyCategoriaIds = Array.from(document.querySelectorAll('[id*="categoria"]'))
        .slice(0, 20)
        .map((el) => el.id)
        .filter(Boolean);

      this.log('#categoria_id encontrado?', 'debug', { length: byIdLen });
      if (!byIdLen) {
        // fallback: alguns formulários podem não estar com id="categoria_id"
        if (byNameLen) {
          this.log('Fallback: select[name="categoria_id"] encontrado, usando ele.', 'warning', { byNameLen });
          $categoria = $('select[name="categoria_id"]').first();
          const existingId = $categoria.attr('id');
          if (!existingId) {
            const targetId = document.getElementById('categoria_id') ? 'categoria_id_select' : 'categoria_id';
            $categoria.attr('id', targetId);
            this.log('Atribuindo id ao select de categoria para estabilizar o Select2', 'warning', {
              assignedId: targetId,
            });
          }
        } else {
          this.categoriaInitAttempts += 1;
          this.log('Diagnóstico DOM (ids contendo "categoria")', 'debug', { anyCategoriaIds });
          this.log('Diagnóstico DOM (select[name=categoria_id])', 'debug', { byNameLen });

          if (this.categoriaInitAttempts <= 12) {
            this.log('Select ainda não existe no DOM, tentando novamente...', 'warning', {
              attempt: this.categoriaInitAttempts,
            });
            setTimeout(() => this.initCategoriaSelect2(), 250);
          } else {
            this.log('Desisti de iniciar categoria: nenhum select de categoria apareceu no DOM.', 'error', {
              attempts: this.categoriaInitAttempts,
              byIdLen,
              byNameLen,
            });
          }
          return;
        }
      }

      if (!$categoria.length) {
        this.categoriaInitAttempts += 1;
        if (this.categoriaInitAttempts <= 12) {
          this.log('Select ainda não existe no DOM, tentando novamente...', 'warning', {
            attempt: this.categoriaInitAttempts,
          });
          setTimeout(() => this.initCategoriaSelect2(), 250);
        } else {
          this.log('Desisti de iniciar categoria: #categoria_id não apareceu no DOM.', 'error', {
            attempts: this.categoriaInitAttempts,
          });
        }
        return;
      }

      try {
        const el = $categoria.get(0);
        this.log('Estado do select', 'debug', {
          id: $categoria.attr('id'),
          name: $categoria.attr('name'),
          className: $categoria.attr('class'),
          tagName: el?.tagName,
          disabled: Boolean(el?.disabled),
          required: Boolean(el?.required),
          value: el?.value,
          optionsCount: el?.options?.length,
          firstOption: el?.options?.[0]?.value,
          firstOptionText: el?.options?.[0]?.text,
          isVisible: $categoria.is(':visible'),
        });
      } catch (e) {
        this.log('Falha ao inspecionar estado do select', 'warning', e);
      }

      if ($categoria.data('select2')) {
        this.log('select2 já existia, destruindo...', 'warning');
        $categoria.select2('destroy');
      }

      const placeholder = this.config.labelSelectCategoria || 'Selecione uma categoria';
      const optionsCount = Number($categoria.get(0)?.options?.length ?? 0);

      // Se o select já veio com opções (ex: carregado pelo backend), não use AJAX.
      // AJAX com term vazio costuma não listar nada ao abrir (parece "travado").
      if (optionsCount > 1) {
        const $modal = $categoria.closest('.modal');
        const dropdownParent = $modal.length ? $modal : $(document.body);
        this.log('Inicializando categoria com $.fn.select2 (opções já presentes)', 'info', {
          optionsCount,
          dropdownParent: $modal.length ? 'modal' : 'body',
        });
        $categoria.select2({
          width: '100%',
          placeholder,
          allowClear: true,
          dropdownParent,
          minimumResultsForSearch: 1,
        });
      } else {
        if (!this.config.categoriaSelectUrl) {
          this.log('categoriaSelectUrl vazio/undefined — select2 ajax não vai listar nada.', 'error');
        }

        this.log('Inicializando categoria com AJAX (select sem opções)', 'info', { optionsCount });
        $categoria.select2({
          width: '100%',
          placeholder,
          allowClear: true,
          minimumInputLength: 0,
          ajax: {
            url: this.config.categoriaSelectUrl,
            dataType: 'json',
            delay: 250,
            data: (params) => {
              const payload = { q: params.term || '' };
              this.log('Select2 data() chamado', 'debug', payload);
              return payload;
            },
            transport: (params, success, failure) => {
              this.log('Select2 transport() request', 'info', {
                url: params?.url,
                type: params?.type,
                data: params?.data,
              });

              const request = $.ajax(params);
              request
                .done((res) => {
                  this.log('Select2 transport() success', 'info', res);
                  success(res);
                })
                .fail((jqXHR, textStatus, errorThrown) => {
                  this.log('Select2 transport() fail', 'error', {
                    textStatus,
                    errorThrown,
                    status: jqXHR?.status,
                    responseText: jqXHR?.responseText,
                  });
                  failure(jqXHR, textStatus, errorThrown);
                });
              return request;
            },
            processResults: (data) => {
              this.log('Select2 processResults() data', 'debug', data);
              return data;
            },
            cache: true,
          },
        });
      }

      try {
        const currentVal = $categoria.val();
        if (currentVal === '' || currentVal == null) {
          $categoria.val(null).trigger('change.select2');
          this.log('Forçando placeholder (val null) pois valor estava vazio', 'info', { currentVal });
        }
      } catch (e) {
        this.log('Falha ao forçar placeholder', 'warning', e);
      }

      $categoria.on('select2:open', () => this.log('select2:open', 'info'));
      $categoria.on('select2:closing', () => this.log('select2:closing', 'info'));
      $categoria.on('select2:select', (e) => this.log('select2:select', 'info', e?.params?.data));
      $categoria.on('select2:clear', () => this.log('select2:clear', 'info'));
      $categoria.on('select2:unselect', (e) => this.log('select2:unselect', 'info', e?.params?.data));

      $categoria.on('select2:open', () => {
        setTimeout(() => {
          const options = Array.from(document.querySelectorAll('.select2-results__option'))
            .slice(0, 5)
            .map((el) => el.textContent?.trim())
            .filter(Boolean);
          const rect = document.querySelector('.select2-dropdown')?.getBoundingClientRect?.();
          this.log('Após open: opções renderizadas?', 'debug', {
            renderedCount: document.querySelectorAll('.select2-results__option').length,
            sample: options,
            dropdownRect: rect
              ? { width: rect.width, height: rect.height, top: rect.top, left: rect.left }
              : null,
          });
        }, 0);
      });

      setTimeout(() => {
        const containerId = `select2-${$categoria.attr('id')}-container`;
        const container = document.getElementById(containerId);
        this.log('Após init: container select2 existe?', 'debug', {
          containerId,
          exists: Boolean(container),
          textContent: container?.textContent,
          title: container?.getAttribute?.('title'),
        });
      }, 0);

      this.log('initCategoriaSelect2() end', 'info');
    }

    initCodigoBarrasInicial() {
      const codigoBarrasInicial = this.config.codigoBarrasInicial;
      if (!codigoBarrasInicial) return;
      const el = document.getElementById('codigo_barras');
      if (el) el.value = codigoBarrasInicial;
    }

    initPreencherSku() {
      const btn = document.getElementById('preencher_SKU');
      if (!btn || !this.elements.inpSku) return;
      btn.addEventListener('click', () => {
        this.elements.inpSku.value = String(this.config.sku ?? '');
      });
    }

    initCodigoInicial() {
      if (!this.elements.inpCodigo) return;
      if (this.elements.inpCodigo.value) return;
      const codigo = String(this.config.codigo ?? '');
      this.elements.inpCodigo.value = codigo;
      const hidden = document.getElementById('codigo_hidden');
      if (hidden) hidden.value = codigo;
    }

    initTinyMce() {
      if (typeof tinymce === 'undefined') return;
      tinymce.init({
        selector: 'textarea.tiny',
        language: 'pt_BR',
      });

      setTimeout(() => {
        $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none');
      }, 1000);
    }

    initSkuHandlers() {
      const input = this.elements.inpSku;
      if (!input) return;

      input.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 5) this.value = this.value.slice(0, 5);
      });

      input.addEventListener('blur', async (e) => {
        const value = e.target?.value;
        if (!value || value.length !== 5) return;

        try {
          const response = await this.searchProductBySKU(value);
          if (!response) return;

          const { nome, id } = response;
          const isEditPage = window.location.pathname.includes('edit');
          const currentId = window.location.pathname.split('/')?.[2];

          if ((isEditPage && currentId !== String(id)) || !isEditPage) {
            if (typeof Swal !== 'undefined') {
              await Swal.fire({
                icon: 'error',
                text: `O código SKU: ${value} já esta em uso pelo produto ${nome}, Tente outro.`,
                timer: 10000,
              });
            }
            input.value = '';
          }
        } catch (err) {
          // mantém o comportamento atual (apenas loga)
          console.error(err);
        }
      });
    }

    initCfopSync() {
      $(document).on('blur', '#inp-cfop_estadual', function () {
        const v = $(this).val().substring(1, 4);
        $('#inp-cfop_outro_estado').val('6' + v);
        $('#inp-cfop_entrada_estadual').val('1' + v);
        $('#inp-cfop_entrada_outro_estado').val('2' + v);
      });
    }

    async searchProductBySKU(code) {
      const idEmitente = this.config.idEmitente;
      if (!idEmitente) return false;

      const baseUrl = typeof path_url !== 'undefined' ? path_url : '/';
      const response = await fetch(`${baseUrl}api/produtos/search-by-sku/${code}/${idEmitente}`, {
        signal: this.abortController?.signal,
      });
      if (!response.ok) return false;
      return await response.json();
    }

    addClassRequiredOnAdd() {
      let isInvalid = false;
      $('.tab-fiscal').trigger('click');
      let campos = '';

      $('body #form-produto')
        .find('input, select')
        .each(function () {
          if ($(this).prop('required')) {
            if ($(this).val() === '' || $(this).val() == null) {
              $(this).addClass('is-invalid');
              isInvalid = true;
              if ($(this).prev()[0]?.textContent) campos += $(this).prev()[0]?.textContent + ', ';
            } else {
              $(this).removeClass('is-invalid');
            }
          } else {
            $(this).removeClass('is-invalid');
          }
        });

      setTimeout(() => {
        if (isInvalid) {
          campos = campos.substring(0, campos.length - 2);
          if (typeof toastr !== 'undefined') toastr.error('Campos obrigatórios não preenchidos: ' + campos);
        } else {
          window.$body?.addClass?.('loading');
        }
      }, 50);
    }

    adicionarCombinacao() {
      const form = this.elements.formProduto;
      if (!form) return;

      if (!form.checkValidity()) {
        form.reportValidity();
        this.addClassRequiredOnAdd();
        return;
      }

      const corEl = document.getElementById('cor_id');
      const tamanhoEl = document.getElementById('tamanho_id');
      const estoqueEl = document.getElementById('estoque_variacao');

      const corId = corEl?.value;
      const corTexto = corId ? corEl.options[corEl.selectedIndex]?.text : 'N/A';
      const tamanhoId = tamanhoEl?.value;
      const tamanhoTexto = tamanhoId ? tamanhoEl.options[tamanhoEl.selectedIndex]?.text : 'N/A';
      const estoque = estoqueEl?.value || 0;
      const codigo = this.elements.codigoVariacao?.value || '';

      if (!corId && !tamanhoId) {
        alert('Por favor, selecione pelo menos uma cor, um tamanho ou ambos.');
        return;
      }

      const existe = this.combinacoes.some(
        (c) => String(c.cor_id ?? '') === String(corId ?? '') && String(c.tamanho_id ?? '') === String(tamanhoId ?? ''),
      );
      if (existe) {
        alert('Essa combinação de cor e tamanho já está cadastrada.');
        return;
      }

      const novaCombinacao = {
        cor_id: corId || null,
        cor_texto: corTexto,
        tamanho_id: tamanhoId || null,
        tamanho_texto: tamanhoTexto,
        estoque: estoque,
        codigo: codigo,
      };

      this.combinacoes = [novaCombinacao];
      const hidden = document.getElementById('combinacoes');
      if (hidden) hidden.value = JSON.stringify(this.combinacoes);

      const methodOverride = document.getElementsByName('_method')?.[0];
      if (methodOverride) methodOverride.remove();

      const baseUrl = typeof path_url !== 'undefined' ? path_url : '/';
      const payload = $('#form-produto').serialize();

      $.post(baseUrl + 'produtos/save', payload)
        .done(async (success) => {
          if (!success?.error) {
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: 'Produto salvo com sucesso!',
                showConfirmButton: false,
                timer: 1500,
              });
            }

            await this.createSKU();
            await this.createCode();
            if (this.elements.codigoBarras) this.elements.codigoBarras.value = '';
            this.limparCampos();
          } else if (success?.msg) {
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'error',
                text: success.msg,
              });
            }
          } else {
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao salvar o produto!',
              });
            }
          }
        })
        .fail((error) => {
          console.log({ error });
        });

      const finalizar = this.elements.btnFinalizar;
      if (finalizar) finalizar.classList.remove('d-none');
      this.elements.btnAction?.classList.add('d-none');
    }

    async createSKU() {
      const idEmitente = this.config.idEmitente;
      if (!idEmitente || !this.elements.inpSku) return false;

      const baseUrl = typeof path_url !== 'undefined' ? path_url : '/';
      const response = await fetch(`${baseUrl}api/produtos/sku-list/${idEmitente}/1`, {});
      if (!response.ok) return false;
      const arr = await response.json();
      this.elements.inpSku.value = arr?.[0] ?? '';
      return true;
    }

    async createCode() {
      const idEmitente = this.config.idEmitente;
      if (!idEmitente || !this.elements.inpCodigo) return false;

      const baseUrl = typeof path_url !== 'undefined' ? path_url : '/';
      const response = await fetch(`${baseUrl}api/produtos/code-list/${idEmitente}/1`, {});
      if (!response.ok) return false;
      const arr = await response.json();
      const codigo = arr?.[0] ?? '';
      this.elements.inpCodigo.value = codigo;
      const hidden = document.getElementById('codigo_hidden');
      if (hidden) hidden.value = codigo;
      return true;
    }

    removerCombinacao(index) {
      if (Number.isNaN(index) || index < 0) return;
      this.combinacoes.splice(index, 1);
      const hidden = document.getElementById('combinacoes');
      if (hidden) hidden.value = JSON.stringify(this.combinacoes);

      const tabelaBody = document.getElementById('tabela-combinacoes-body');
      if (!tabelaBody) return;

      tabelaBody.innerHTML = '';
      this.combinacoes.forEach((c, i) => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${c.cor_texto ?? 'N/A'}</td>
          <td>${c.tamanho_texto ?? 'N/A'}</td>
          <td>${c.estoque ?? 0}</td>
          <td>${c.codigo ?? ''}</td>
          <td>
            <button type="button" class="btn btn-danger btn-sm" data-index="${i}">
              Remover
            </button>
          </td>
        `;
        tabelaBody.appendChild(row);
        row.querySelector('button')?.addEventListener('click', (e) => {
          const idx = Number(e.currentTarget?.getAttribute('data-index'));
          this.removerCombinacao(idx);
        });
      });
    }

    limparCampos() {
      const estoqueEl = document.getElementById('estoque_variacao');
      if (estoqueEl) estoqueEl.value = '';
      if (this.elements.codigoVariacao) this.elements.codigoVariacao.value = '';
      if (this.elements.codigoBarras) this.elements.codigoBarras.value = '';
    }

    initCombinacoes() {
      const isEditPage = window.location.pathname.includes('edit');
      if (!isEditPage) return;

      this.combinacoes.forEach((combinacao) => {
        if (this.elements.codigoVariacao) this.elements.codigoVariacao.value = combinacao?.codigo;
        if (this.elements.codigoBarras) this.elements.codigoBarras.value = combinacao?.codigo;
        const estoqueEl = document.getElementById('estoque_variacao');
        if (estoqueEl) estoqueEl.value = combinacao?.estoque;

        const corEl = document.getElementById('cor_id');
        if (corEl) corEl.value = combinacao?.cor_id;
        const corContainer = document.getElementById('select2-cor_id-container');
        if (corContainer) {
          corContainer.innerHTML = combinacao?.cor_texto;
          corContainer.title = combinacao?.cor_texto;
        }

        const tamanhoEl = document.getElementById('tamanho_id');
        if (tamanhoEl) tamanhoEl.value = combinacao?.tamanho_id;
        const tamanhoContainer = document.getElementById('select2-tamanho_id-container');
        if (tamanhoContainer) {
          tamanhoContainer.innerHTML = combinacao?.tamanho_texto;
          tamanhoContainer.title = combinacao?.tamanho_texto;
        }
      });

      const hidden = document.getElementById('combinacoes');
      if (hidden) hidden.value = JSON.stringify(this.combinacoes);

      const btn = this.elements.btnAction;
      const form = this.elements.formProduto;
      if (!btn || !form) return;

      btn.type = 'button';
      btn.addEventListener('click', () => {
        const novaCombinacao = {
          cor_id: null,
          cor_texto: 'N/A',
          tamanho_id: null,
          tamanho_texto: null,
          estoque: null,
          codigo: null,
        };
        this.combinacoes = [novaCombinacao];
        if (hidden) hidden.value = JSON.stringify(this.combinacoes);

        if (form.checkValidity()) {
          form.submit();
        } else {
          form.reportValidity();
          this.addClassRequiredOnAdd();
        }
      });
    }

    initVariacaoSync() {
      const estoqueVar = document.getElementById('estoque_variacao');
      if (estoqueVar && this.elements.inpEstoqueInicial) {
        estoqueVar.addEventListener('blur', (e) => {
          this.elements.inpEstoqueInicial.value = e.target.value;
        });
      }

      if (this.elements.codigoVariacao && this.elements.codigoBarras) {
        this.elements.codigoVariacao.addEventListener('change', (e) => {
          this.elements.codigoBarras.value = e.target.value;
        });
      }
    }

    initFinalizar() {
      const btnFinalizar = this.elements.btnFinalizar;
      if (!btnFinalizar) return;
      btnFinalizar.addEventListener('click', () => {
        window.location.replace('/produtos');
      });
    }

    gerarCodeEstoque() {
      const baseUrl = typeof path_url !== 'undefined' ? path_url : '/';
      $.get(baseUrl + 'produtos-gerar-codigo-ean').done((res) => {
        if (this.elements.codigoVariacao) this.elements.codigoVariacao.value = res;
        if (this.elements.codigoBarras) this.elements.codigoBarras.value = res;
      });
    }

    initImagemPreview() {
      const produtoInput = document.getElementById('produto-image');
      const removedInput = document.querySelector('input[name="removed_image"]');
      const previewWrapper = document.getElementById('uploaded_produto_imagem');
      const uploadButton = document.getElementById('btn-subir-imagem');

      const defaultPreviewMarkup =
        '<span data-icon="solar:user-circle-linear" class="iconify fs-40" aria-hidden="true"></span>';

      const renderPreview = (src) => {
        if (!previewWrapper) return;
        if (!src) {
          previewWrapper.innerHTML = defaultPreviewMarkup;
          return;
        }

        previewWrapper.innerHTML = `
          <img
            src="${src}"
            alt="Imagem principal"
            class="img-fluid"
            style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;"
          >
        `;
      };

      if (uploadButton && produtoInput) {
        uploadButton.addEventListener('click', (event) => {
          event.preventDefault();
          produtoInput.click();
        });
      }

      if (produtoInput) {
        produtoInput.addEventListener('change', (event) => {
          const file = event.target.files[0];
          if (!file) return;
          if (removedInput) removedInput.value = '';
          const reader = new FileReader();
          reader.onload = (e) => renderPreview(e.target.result);
          reader.readAsDataURL(file);
        });
      }

      window.removerImagemProduto = () => {
        if (produtoInput) produtoInput.value = '';
        if (removedInput) removedInput.value = '1';
        renderPreview(null);
      };
    }

    bindLegacyGlobals() {
      window.adicionarCombinacao = () => this.adicionarCombinacao();
      window.limparCampos = () => this.limparCampos();
      window.gerarCode_estoque = () => this.gerarCodeEstoque();
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const config = window.ProdutoFormsConfig || {};
    new ProdutoFormsPage(config).init();
  });
})();
