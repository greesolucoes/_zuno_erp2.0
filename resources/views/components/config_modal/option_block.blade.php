@php
    $url = $route ? route($route, $route_id ?? null) : '#';
    if (!empty($page_position)) {
        $url .= '#' . $page_position;
    }
@endphp

<a 
    class=" 
        {{isset($small) ? 'list-group-item-sm' : 'list-group-item'}}
        {{isset($disabled) && $disabled == true ? 'disabled' : ''}}
    " 
    href="{{ isset($disabled) && $disabled == true ? '' : $url}}"
    {!! $custom_attributes ?? 'onclick="$(\'#modal-configuracao\').modal(\'hide\')"' !!}
>
    <span>
        <img src="/assets/images/configuracoes/{{ $icon }}" alt="{{ $label }}" width="32" height="32" />
    </span>
    <p class="m-0 p-0">
        {{ $label }}
    </p>
</a>
