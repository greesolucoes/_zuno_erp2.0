<div class="row">
  <div class="col-12">
    <h3 class="mb-4 text-color">Anexar fotos do Checklist</h3>

    <div class="mc__anexo-container" name="anexo-container">
        <div class="mc__anexo-block" name="anexo-block">
            <div class="image-area">
                <div class="remove-image-btn btn-danger">X</div>
                <div style="width: 100%">
                    <img src="{{ asset('/imgs/no-image.png') }}" alt="Anexo" class="mc__anexo">
                </div>
            </div>
            <div class="btn btn-success btn-file w-100">
                <i class="ri-download-2-line"></i> Procurar arquivo
            </div>
            <input accept="image/*" name="anexos[]" type="file" class="d-none">
            <input type="hidden" name="anexos_url[]">
        </div>

        <button type="button" class="add-image-btn" title="Adicionar mais um anexo">
            <i class="ri-add-line"></i>
        </button>
    </div>
  </div>
</div>
