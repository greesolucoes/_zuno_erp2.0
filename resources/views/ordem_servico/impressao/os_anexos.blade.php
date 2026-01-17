<div width="100%" >
    <div>
        <p class="anexo-title"><strong>Fotos</strong></p>
        @foreach ($ordem->anexos as $anexo)
            <div class="anexo-item">
                <img src="{{ $anexo }}"/>
            </div>
        @endforeach
    </div>
</div>