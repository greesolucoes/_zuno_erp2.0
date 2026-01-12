@php
$menu = new App\Helpers\Menu();
$menu = $menu->preparaMenu();
@endphp

@foreach($menu as $m)
@if(!isset($m['ativo']) || $m['ativo'])
<li @if($m['titulo'] == $rotaAtiva) class="mm-active" @endif>
	<a href="javascript:;" class="has-arrow">
		<div class="parent-icon"><i class='{{$m['icone']}}'></i>
		</div>
		<div class="menu-title">{{$m['titulo']}}</div>
	</a>
	<ul>	
		@if(($m['titulo'] ?? '') === 'Pet Shop')
			@php
				$groups = [
					'cadastros' => ['label' => 'Cadastros', 'items' => []],
					'planos' => ['label' => 'Planos', 'items' => []],
					'veterinario' => ['label' => 'Atd. Veterinário', 'items' => []],
					'hotel' => ['label' => 'Hotel', 'items' => []],
					'creche' => ['label' => 'Creche', 'items' => []],
					'estetica' => ['label' => 'Estética', 'items' => []],
					'tele_entregas' => ['label' => 'Tele-entregas', 'items' => []],
					'outros' => ['label' => 'Outros', 'items' => []],
				];
				$standaloneItems = [];

				foreach (($m['subs'] ?? []) as $subItem) {
					$name = $subItem['nome'] ?? '';
					if (in_array($name, ['Agenda', 'Configurações Pet Shop'], true)) {
						$standaloneItems[] = $subItem;
						continue;
					}
					$groupKey = null;
					$label = $name;

					if (\Illuminate\Support\Str::startsWith($name, 'Veterinário - ')) {
						$groupKey = 'veterinario';
						$label = trim(substr($name, strlen('Veterinário - ')));
					} elseif (\Illuminate\Support\Str::startsWith($name, 'Hotel - ')) {
						$groupKey = 'hotel';
						$label = trim(substr($name, strlen('Hotel - ')));
					} elseif (\Illuminate\Support\Str::startsWith($name, 'Creche - ')) {
						$groupKey = 'creche';
						$label = trim(substr($name, strlen('Creche - ')));
					} elseif (\Illuminate\Support\Str::startsWith($name, 'Estética - ')) {
						$groupKey = 'estetica';
						$label = trim(substr($name, strlen('Estética - ')));
					} elseif (\Illuminate\Support\Str::startsWith($name, 'Tele-entregas - ') || $name === 'Tele-entregas') {
						$groupKey = 'tele_entregas';
						if (\Illuminate\Support\Str::startsWith($name, 'Tele-entregas - ')) {
							$label = trim(substr($name, strlen('Tele-entregas - ')));
						}
					} elseif (in_array($name, ['Cliente Plano', 'Cliente Avulso (Portal)', 'Gerenciar Planos'], true)) {
						$groupKey = 'planos';
					} elseif (in_array($name, ['Pets', 'Espécies', 'Raças', 'Pelagens'], true)) {
						$groupKey = 'cadastros';
					} else {
						$groupKey = 'outros';
					}

					$subItem['nome'] = $label;
					$groups[$groupKey]['items'][] = $subItem;
				}

				$renderLeaf = function ($item) {
					$mostrarSempre = $item['mostrar_sempre'] ?? false;
					$disabled = isset($item['rota_ativa']) && $item['rota_ativa'] === false;
					$shouldRender = ($item['rota'] ?? '') !== '' && (!isset($item['rota_ativa']) || $mostrarSempre);

					if (! $shouldRender) return '';

					if ($disabled) {
						return '<li><a class="text-muted" href="javascript:;" style="pointer-events:none;opacity:.6"><i class="bx bx-circle" style="font-size: 10px;"></i>' . e($item['nome']) . '</a></li>';
					}

					$target = isset($item['target']) ? ' target="_blank"' : '';
					return '<li><a' . $target . ' href="' . e($item['rota']) . '"><i class="bx bx-circle" style="font-size: 10px;"></i>' . e($item['nome']) . '</a></li>';
				};
			@endphp

			@foreach($standaloneItems as $standaloneItem)
				{!! $renderLeaf($standaloneItem) !!}
			@endforeach

			@foreach($groups as $group)
				@php
					$items = $group['items'] ?? [];
				@endphp
				@if(count($items) === 0)
					@continue
				@endif

				<li>
					<a href="javascript:;" class="has-arrow">
						<i class="bx bx-circle" style="font-size: 10px;"></i>{{ $group['label'] }}
					</a>
					<ul>
						@foreach($items as $subItem)
							{!! $renderLeaf($subItem) !!}
						@endforeach
					</ul>
				</li>
			@endforeach
		@else
			@foreach($m['subs'] as $i)
			@php
				$mostrarSempre = $i['mostrar_sempre'] ?? false;
				$disabled = isset($i['rota_ativa']) && $i['rota_ativa'] === false;
			@endphp
			@if($i['rota'] != '' && (!isset($i['rota_ativa']) || $mostrarSempre))
				<li>
					@if($disabled)
						<a class="text-muted" href="javascript:;" style="pointer-events:none;opacity:.6">
							<i class="bx bx-circle" style="font-size: 10px;"></i>{{$i['nome']}}
						</a>
					@else
						<a @isset($i['target']) target="_blank" @endisset href="{{$i['rota']}}">
							<i class="bx bx-circle" style="font-size: 10px;"></i>{{$i['nome']}}
						</a>
					@endif
				</li>
			@endif
			@endforeach
		@endif
	</ul>
</li>
@endif
@endforeach
