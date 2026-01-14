<div class="row g-3">
	<div class="col-md-6">
		{!! Form::text('nome', 'Nome')->required() !!}
	</div>

	<div class="col-md-6">
		{!! Form::select('perfil_preset', 'Sugestão de perfil (opcional)', ['' => 'Selecione'] + collect($perfilPresets ?? [])->pluck('nome', 'key')->all())
			->attrs(['class' => 'form-select', 'id' => 'perfil_preset']) !!}
		<small class="text-muted">Ao selecionar, as permissões serão marcadas automaticamente.</small>
	</div>

	<div class="col-12 mt-2">
		<h5 class="mb-2">Permissão de Acesso</h5>
		<small class="text-muted">Clique no módulo para abrir/fechar as permissões.</small>
	</div>

	<input type="hidden" id="perfil_presets" value='@json($perfilPresets ?? [])'>

	<input type="hidden" id="menus" value="{{ json_encode($menu) }}" name="">

	<div class="col-12">
		@php
			$total = count($menu ?? []);
			$half = (int) ceil($total / 2);
			$menusLeft = array_slice($menu, 0, $half);
			$menusRight = array_slice($menu, $half);
		@endphp

		<div class="row">
			<div class="col-12 col-lg-6">
				<div class="accordion" id="accordionPermissoesLeft">
					@foreach ($menusLeft as $m)
						@php
							$groupKey = preg_replace('/[^a-zA-Z0-9_\\-]/', '_', $m['titulo']);
							$collapseId = 'collapse_left_' . $groupKey;
							$headingId = 'heading_left_' . $groupKey;
						@endphp
						<div class="accordion-item">
							<h2 class="accordion-header" id="{{ $headingId }}">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
									{{ $m['titulo'] }}
								</button>
							</h2>
							<div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headingId }}" data-bs-parent="#accordionPermissoesLeft">
								<div class="accordion-body">
									<div class="mb-2">
										<label class="checkbox checkbox-info">
											<input id="todos_{{ str_replace(' ', '_', $m['titulo']) }}" onclick="marcarTudo('{{ $m['titulo'] }}')" type="checkbox">
											<span></span><strong class="text-info" style="margin-left: 5px;">Marcar tudo</strong>
										</label>
									</div>

									<div class="checkbox-inline">
										@foreach ($m['subs'] as $s)
											@if ($s['nome'] != 'NFS-e')
												@php
													$link = str_replace('/', '', $s['rota']);
													$link = str_replace('.', '_', $link);
													$link = str_replace(':', '_', $link);
												@endphp
												<label class="checkbox checkbox-info check-sub">
													<input id="sub_{{ $link }}" @if (\App\Models\Empresa::validaLink($s['rota'], $permissoesAtivas)) checked @endif type="checkbox" name="{{ $s['rota'] }}">
													<span></span>{{ $s['nome'] }}
												</label>
											@endif
										@endforeach
									</div>
								</div>
							</div>
						</div>
					@endforeach
				</div>
			</div>

			<div class="col-12 col-lg-6 mt-3 mt-lg-0">
				<div class="accordion" id="accordionPermissoesRight">
					@foreach ($menusRight as $m)
						@php
							$groupKey = preg_replace('/[^a-zA-Z0-9_\\-]/', '_', $m['titulo']);
							$collapseId = 'collapse_right_' . $groupKey;
							$headingId = 'heading_right_' . $groupKey;
						@endphp
						<div class="accordion-item">
							<h2 class="accordion-header" id="{{ $headingId }}">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
									{{ $m['titulo'] }}
								</button>
							</h2>
							<div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headingId }}" data-bs-parent="#accordionPermissoesRight">
								<div class="accordion-body">
									<div class="mb-2">
										<label class="checkbox checkbox-info">
											<input id="todos_{{ str_replace(' ', '_', $m['titulo']) }}" onclick="marcarTudo('{{ $m['titulo'] }}')" type="checkbox">
											<span></span><strong class="text-info" style="margin-left: 5px;">Marcar tudo</strong>
										</label>
									</div>

									<div class="checkbox-inline">
										@foreach ($m['subs'] as $s)
											@if ($s['nome'] != 'NFS-e')
												@php
													$link = str_replace('/', '', $s['rota']);
													$link = str_replace('.', '_', $link);
													$link = str_replace(':', '_', $link);
												@endphp
												<label class="checkbox checkbox-info check-sub">
													<input id="sub_{{ $link }}" @if (\App\Models\Empresa::validaLink($s['rota'], $permissoesAtivas)) checked @endif type="checkbox" name="{{ $s['rota'] }}">
													<span></span>{{ $s['nome'] }}
												</label>
											@endif
										@endforeach
									</div>
								</div>
							</div>
						</div>
					@endforeach
				</div>
			</div>
		</div>
	</div>

	<div class="col-12">
		<button type="submit" class="btn btn-primary px-5">Salvar</button>
	</div>
</div>

@section('js')
<script type="" src="/js/perfil.js"></script>
@endsection
