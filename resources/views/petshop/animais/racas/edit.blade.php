@extends('default.layout', ['title' => 'Atualizar raça'])

@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('animais.racas.index', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>

			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Atualizar raça</h5>
			</div>
			<hr>

			{!!Form::open()->fill($item)
			->put()
			->id('form-racas')
			->route('animais.racas.update', [$item->id])
			->multipart()
			!!}
			<div class="pl-lg-4">
				@include('petshop.animais.racas._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection
