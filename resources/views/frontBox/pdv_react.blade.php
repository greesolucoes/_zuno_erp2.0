<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Frente de Caixa</title>
    @vite('resources/js/pdv.tsx')
    <style>
        body { margin: 0; }
    </style>
</head>
<body>
<div id="pdv-react-root"></div>
<script>
    window.__PDV_PROPS__ = @json($props ?? []);
</script>
</body>
</html>

