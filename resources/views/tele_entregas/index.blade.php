@extends('default.layout',['title' => 'Tele-Entregas'])

@section('content')
<div class="page-content">
	<div class="card ">
		<div class="card-body p-4">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('tele_entregas.create')}}" type="button" class="btn btn-success">
						<i class="bx bx-plus"></i> Nova tele-entrega
					</a>
				</div>
			</div>
			<div class="col">
				<h6 class="mb-0 text-uppercase">Tele-entregas</h6>

				{!!Form::open()->fill(request()->all())
				->get()
				!!}
				<div class="row">
					<div class="col-md-3">
						{!!Form::text('pesquisa', 'Pesquisar por cliente')
						!!}
					</div>
					<div class="col-md-3">
						{!!Form::select('status', 'Status', [
						'' => 'Todos',
						'pendente' => 'Pendente',
						'entregue' => 'Entregue',
						'cancelado' => 'Cancelado',
						])
						->attrs(['class' => 'form-select select2'])
						!!}
					</div>
					<div class="col-md-3 text-left ">
						<br>
						<button class="btn btn-primary"  type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
						<a id="clear-filter" class="btn btn-danger"
						href="{{ route('tele_entregas.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
					</div>
				</div>

				{!!Form::close()!!}

				<hr/>
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table mb-0 table-striped">
								<thead class="">
									<tr>
										<th>Cliente</th>
										<th>Endereço</th>
										<th>Valor</th>
										<th>Pago</th>
										<th>Status</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody>
									@forelse($data as $item)
									<tr>
										<td>{{ $item->cliente->razao_social ?? $item->cliente->nome_fantasia }}</td>
										<td>{{ $item->rua }}, {{ $item->numero }} - {{ $item->complemento }}</td>
										<td>R$ {{ __moeda($item->valor) }}</td>
										<td>{{ $item->getFoiPago() }}</td>
										<td>{{ $item->getStatus() }}</td>
										<td>
											<form action="{{ route('tele_entregas.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
												@method('delete')
												<a href="{{ route('tele_entregas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white">
													<i class="bx bx-edit"></i>
												</a>

												@csrf
												<button type="button" class="btn btn-delete btn-sm btn-danger">
													<i class="bx bx-trash"></i>
												</button>
											</form>
										</td>
									</tr>
									@empty
									<tr>
										<td colspan="6" class="text-center">Nada encontrado</td>
									</tr>
									@endforelse
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			{!! $data->appends(request()->all())->links() !!}
		</div>
	</div>
</div>
@endsection
