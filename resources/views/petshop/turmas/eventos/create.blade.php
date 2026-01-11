@extends('default.layout',['title' => 'Novo Evento de Turma'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('turmas.eventos.index', ['turma_id' => $turmaId]) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Novo evento de turma</h5>
			</div>
			<hr>

			{!!Form::open()
			->post()
			->route('turmas.eventos.store')!!}
			<div class="pl-lg-4">
				@include('petshop.turmas.eventos._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection
