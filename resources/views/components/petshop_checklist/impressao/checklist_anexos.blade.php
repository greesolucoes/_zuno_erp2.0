<div width="100%" >
    <div>
        <p class="anexo-title"><strong>ANEXOS</strong></p>
        @foreach ($item['anexos'] as $anexo)
            <div class="anexo-item">
                <img src="{{ $anexo }}"/>
            </div>
        @endforeach
    </div>
</div>