@section('css')
    <style type="text/css">
        .rights-container {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: var(--footer-height);

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 0 16px;
            font-size: 0.85rem;
            text-align: center;

            background: transparent;
            z-index: 1000;
        }
    </style>
@endsection

<footer class="rights-container">
    <p class="text-white text-center">
        Todos os direitos reservados.
        {{ env('APP_NAME') }}
        ©
        <script>
            document.write(new Date().getFullYear());
        </script>
    </p>
</footer>   
