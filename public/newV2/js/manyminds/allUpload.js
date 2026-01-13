$.fn.extend({
	allUpload: function (inputName, inputBlob, actionVisualize, repository, texts, itemCallback, concludeCallback, isOrderable, extensionAccept) {
		let container = $(this);
		let pathToImages = $(container).data("path_previews");
		let maxLimitFiles = $('div#documentos_anexo').data('max_limit_files'); // qtde maximo de arquivos permitidos

		if(!texts) {
			texts = {};
		}
		if (!texts.hasOwnProperty('textUpload')) {
			texts['textUpload'] = "Clique para selecionar os arquivos";
		}
		if (!texts.hasOwnProperty('textVisualize')) {
			texts['textVisualize'] = "Visualizar";
		}
		if (!texts.hasOwnProperty('noDocsText')) {
			texts['noDocsText'] = "Não há documentos!";
		}
		if (!texts.hasOwnProperty('obsText')) {
			texts['obsText'] = "Os documentos aqui exibidos em formato quadrado não representam a sua exibição final. Será possível visualizá-las em sua forma normal...";
		}

		if (!isOrderable) {
			isOrderable = false;
		}

		if(pathToImages === undefined || !pathToImages) {
			pathToImages = null;
		}

		if (!extensionAccept) {
			extensionAccept = '.gif,.jpg,.jpeg,.png,.bmp,.tga,.csv,.xls,.xlsx,.pdf,.doc,.docx,.txt';
		} else {
			if (Array.isArray(extensionAccept)) {
				extensionAccept = extensionAccept.join(',');
			} else if (!(typeof extensionAccept == 'string')) {
				extensionAccept = '';
			}
		}

		if (!inputName) {
			inputName = 'upload-name[]';
		}
		let inputId = inputName;

		inputId = inputId.split("[").join("");
		inputId = inputId.split("]").join("");

		if (!inputBlob) {
			inputBlob = 'upload-blob[]';
		}

		if (!repository) {
			repository = '.preview-docs-zone';
		}

		//área gráfica base
		let repositoryHtml = '';
		if ($(container).find(repository).length === 1) {
			repositoryHtml = $(repository).html();
			$(container).find(repository).remove();
		}

		$(container).append(
			'<div class="allUpload">' +
			'	<fieldset class="form-group link-adiciona-files">' +
			'		<a href="javascript:void(0)" class="allTagsAction">' +
			'			<i class="fas fa-cloud-upload-alt"></i> ' + texts['textUpload'] +
			'		</a>' +
			'		<input type="file" style="display: none;" class="form-control uploadControl" accept="' + extensionAccept + '" multiple="multiple">' +
			'	</fieldset>' +
			'	<div class="preview-docs-zone">' + repositoryHtml + '</div>' +
			'</div>'
		);
		repositoryHtml = null;

		let contentContainer = $(container).find('div.allUpload');
		repository = $(contentContainer).find('div.preview-docs-zone');
		let uploadControl = $(contentContainer).find('input.uploadControl');

		$(container).find('a.allTagsAction').off('click');
		$(container).find('a.allTagsAction').on('click', function () {
			$(uploadControl).trigger('click');
		});
		//área gráfica base

		//adição de arquivos
		let uploadFrontEndNum = 0;
		$(uploadControl).off('change');
		$(uploadControl).on('change', function (ev) {
			if (window.File && window.FileList && window.FileReader) {
				let files = ev.target.files; //FileList object
				let file = null;
				let docReader = null;

				let uploadFrontEndNumFinal = uploadFrontEndNum + files.length;

				// valida a qtde maxima de arquivos que pode enviar
				// Coloque a tag 'data-max_limit_files' na div id=documentos_anexo no html conforme exemplo abaixo
				// <div id="documentos_anexo" data-max_limit_files="3"></div>
				// nao faz validacao se a tag nao existir
				// ele soma a qtde de arquivos já inseridos com o que foi adicionado
				if(!is_empty(maxLimitFiles, 1)) {
					if( ( $('.preview-docs-zone .doc-zone').length + files.length) > maxLimitFiles) {
						// alert('Limite de apenas ' + maxLimitFiles + ' anexos');
						swal({
							title: l["atenção!"],
							text:  l["quantidadeDeAnexosLimitadaApenasA"] + maxLimitFiles,
							type: "warning",
							showCancelButton: false,
							confirmButtonColor: '#3085d6'
						});
						return;
					}
				}

				for (let i = 0; i < files.length; i++) {
					file = files[i];

					docReader = new FileReader();
					docReader.fileName = file.name
					docReader.addEventListener('load', function (event) {
						uploadFrontEndNum++;
						let id = inputId + '-' + uploadFrontEndNum + '-' + (new Date().getUTCMilliseconds());
						let docFile = event.target;
						let docSize = event.total;

						addDocPreview(id, docFile.result, event.target.fileName, uploadFrontEndNum);
						id = null;

						if (uploadFrontEndNum == uploadFrontEndNumFinal && concludeCallback) {
							concludeCallback();
						}
					});

					docReader.readAsDataURL(file);
				}
				$(uploadControl).val('');

				files = null;
				file = null;
				docReader = null;
			} else {
				console.log('Browser not support AllUpload');
			}
		});

		function addDocPreview(id, src, name, num, data) {
			let visualize = null;
			let img       = null;
			let srcCheck  = null;
			if (typeof src == 'string' && src) {
				if(src.toLowerCase().split(";")[0].includes("image")) {
					img = src;
				}
			}
			srcCheck = null;
			if(img) {
				img = '<img id="pro-doc-' + num + '" src="' + img + '" />';
			} else {
				img = '<div class="text-zone">No preview avaible</div>';
			}

			visualize = "";
			if (actionVisualize) {
				visualize = isOldLayout ?
					'<a href="#action" data-no="' + num + '" class="btn btn-light btn-edit-doc action-visualize">'
						+ texts['textVisualize'] +
					'</a>' :
					'<a href="#action" data-no="' + num + '" class="button-form primary-button btn-edit-doc action-visualize text-decoration-none w-50">'
						+ texts['textVisualize'] +
					'</a>';
			}
			if(!name) {
				name = id;
			}
			name = name.split('"').join("'");

			let html =
				'<div id="' + id + '" class="preview-doc preview-show-' + num + '">' +
				'	<div class="doc-cancel" data-no="' + num + '">x</div>' +
				'	<div class="doc-zone' + (isOrderable ? ' orderable' : '') + '">' +
						img +
				'	</div>' +
				'	<div class="tools-edit-doc">' +
						visualize +
				'	</div>' +
				'	<input class="noEffect file-name" style="display: none;" name="' + inputName + '" value="' + name + '" />' +
				'	<textarea class="noEffect file-blob" style="display: none;" name="' + inputBlob + '">' + src + '</textarea>' +
				'</div>';
			img       = null;
			visualize = null;

			$(repository).append(html);
			if (data) {
				$(repository).find('#' + id).data(data);
			}

			$(repository).find('div.doc-cancel').off('click');
			$(repository).find('div.doc-cancel').on('click', function () {
				let no = $(this).data('no');
				if (no) {
					$(repository).find(".preview-doc.preview-show-" + no).remove();
					allUploadMessageEmpty();
					if (concludeCallback) {
						concludeCallback();
					}
				}
			});

			$(repository).find('a.action-visualize').off('click');
			$(repository).find('a.action-visualize').on('click', function () {
				if (actionVisualize) {
					actionVisualize(this);
				} else {
					alert('"function actionVisualize(obj){}" not exists!')
				}
			});

			if (itemCallback) {
				itemCallback($(repository).find('#' + id));
			}

			allUploadMessageEmpty();
		}

		//adição de docs
		$(container).append("<small class=\"form-text text-muted\">" + texts['obsText'] + "</small>");

		$(repository).find('img').each(function () {
			uploadFrontEndNum++;
			let id = inputId + '-' + uploadFrontEndNum + '-' + (new Date().getUTCMilliseconds());
			let src = $(this).attr('src');
			let nome = $(this).data("name");
			let data = $(this).data();

			$(this).remove();
			addDocPreview(id, src, nome, uploadFrontEndNum, data);
			if (concludeCallback) {
				concludeCallback();
			}

			id = null;
			src = null;
		});

		function allUploadMessageEmpty() {
			$(repository).find('#messageEmpty').remove();
			if (!$(repository).find('div.preview-doc').length) {
				$(repository).html('<div id="messageEmpty" class="text-center"><i class="fas fa-paperclip"></i> ' + texts['noDocsText'] + '</div>');
				if (isOrderable) {
					$(repository).sortable("destroy");
				}
			} else {
				if (isOrderable) {
					$(repository).sortable();
				}
			}
		}

		allUploadMessageEmpty();
	}
});