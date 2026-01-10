@extends('layouts.app', ['title' => 'Faturamento do atendimento'])

@section('content')
    @php
        $backUrl = route('vet.atendimentos.index', ['page' => request()->query('page', 1)]);
        $encounterId = data_get($encounter ?? [], 'id');
        $billingExists = $billingExists ?? false;
        $billingTotals = collect(data_get($billingSummary ?? [], 'totals', []));
        $servicesTotalDisplay = $billingTotals->get('services', '0,00');
        $productsTotalDisplay = $billingTotals->get('products', '0,00');
        $grandTotalDisplay = $billingTotals->get('grand_total', '0,00');
        $defaultButtonText = $billingExists ? 'Atualizar faturamento' : 'Salvar faturamento';
        $loadingButtonText = $billingExists ? 'Atualizando...' : 'Salvando...';
        $observationsValue = old('observacoes', $initialObservations ?? '');
    @endphp

    <div class="card">
        <div class="card-header d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row gap-3">
            <div>
                <h3 class="text-color mb-1">Faturamento do atendimento</h3>
                <p class="text-muted small mb-0">
                    Monte a cobrança selecionando serviços e produtos já cadastrados no sistema.
                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if ($encounterId)
                    <a href="{{ route('vet.atendimentos.history', $encounterId) }}" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 px-3">
                        <i class="ri-history-line"></i>
                        Histórico
                    </a>
                @endif
                <a href="{{ $backUrl }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-3">
                    <i class="ri-arrow-left-double-fill"></i>
                    Voltar
                </a>
            </div>
        </div>

        <div class="card-body">
            @if ($billingExists)
                <div class="alert alert-info d-flex align-items-start gap-2">
                    <i class="ri-edit-line fs-5"></i>
                    <div>
                        <strong class="d-block mb-1">Faturamento já registrado</strong>
                        <span class="small">Os itens listados abaixo foram carregados do faturamento existente. Ajuste os valores conforme necessário e salve para atualizar.</span>
                    </div>
                </div>
            @endif
            <form
                id="vet-billing-form"
                class="vet-billing-form"
                method="POST"
                action="{{ $encounterId ? route('vet.atendimentos.billing.store', $encounterId) : '' }}"
            >
                @csrf
                <input type="hidden" name="atendimento_id" value="{{ $encounterId }}">
                @if (!empty($billingId))
                    <input type="hidden" name="billing_id" value="{{ $billingId }}">
                @endif

                <div class="mb-5">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="mb-1 text-color">Serviços</h5>
                            <p class="text-muted small mb-0">Relacione os serviços prestados durante o atendimento.</p>
                        </div>
                        <button type="button" class="btn btn-dark btn-sm vet-billing-add-service d-flex align-items-center gap-1 px-3">
                            <i class="ri-add-fill"></i>
                            Adicionar serviço
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dynamic align-middle vet-billing-services">
                            <thead class="table-light">
                                <tr>
                                    <th width="38%">Serviço</th>
                                    <th width="18%">Data</th>
                                    <th width="18%">Horário</th>
                                    <th width="18%">Valor</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="vet-billing-services-body">
                                @php($servicesPreset = collect($initialServices ?? []))
                                @forelse ($servicesPreset as $service)
                                    @include('petshop.vet.atendimentos.partials.billing.service-row', ['row' => $service])
                                @empty
                                    @include('petshop.vet.atendimentos.partials.billing.service-row')
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="bg-light">
                                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                                            <span class="text-muted small">
                                                {{ $serviceOptionsCount }} serviços disponíveis para faturamento.
                                            </span>
                                            <strong class="text-color">Total de serviços: <span class="vet-billing-services-total">R$ {{ $servicesTotalDisplay }}</span></strong>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="mb-5">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="mb-1 text-color">Produtos</h5>
                            <p class="text-muted small mb-0">Inclua os produtos utilizados ou vendidos neste atendimento.</p>
                        </div>
                        <button type="button" class="btn btn-dark btn-sm vet-billing-add-product d-flex align-items-center gap-1 px-3">
                            <i class="ri-add-fill"></i>
                            Adicionar produto
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dynamic align-middle vet-billing-products">
                            <thead class="table-light">
                                <tr>
                                    <th width="38%">Produto</th>
                                    <th width="15%">Qtd</th>
                                    <th width="18%">Valor unit.</th>
                                    <th width="18%">Subtotal</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="vet-billing-products-body">
                                @php($productsPreset = collect($initialProducts ?? []))
                                @forelse ($productsPreset as $product)
                                    @include('petshop.vet.atendimentos.partials.billing.product-row', ['row' => $product])
                                @empty
                                    @include('petshop.vet.atendimentos.partials.billing.product-row')
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="bg-light">
                                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                                            <span class="text-muted small">
                                                {{ $productOptionsCount }} produtos disponíveis para faturamento.
                                            </span>
                                            <strong class="text-color">Total de produtos: <span class="vet-billing-products-total">R$ {{ $productsTotalDisplay }}</span></strong>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="vet-billing-observacoes" class="form-label text-color">Observações do faturamento</label>
                    <textarea
                        id="vet-billing-observacoes"
                        class="form-control"
                        name="observacoes"
                        rows="3"
                        placeholder="Detalhes adicionais sobre a cobrança"
                    >{{ $observationsValue }}</textarea>
                    <small class="text-muted">Este campo é opcional e pode ser usado para registrar acordos com o tutor ou observações internas.</small>
                </div>

                <div class="border rounded p-3 bg-light d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <h5 class="text-color mb-1">Resumo da cobrança</h5>
                        <p class="text-muted small mb-0">Valores atualizados automaticamente conforme os itens adicionados.</p>
                    </div>
                    <div class="text-md-end">
                        <div class="text-muted small">Serviços: <span class="vet-billing-services-total">R$ {{ $servicesTotalDisplay }}</span></div>
                        <div class="text-muted small">Produtos: <span class="vet-billing-products-total">R$ {{ $productsTotalDisplay }}</span></div>
                        <div class="fs-4 fw-semibold text-success">Total geral: <span class="vet-billing-grand-total">R$ {{ $grandTotalDisplay }}</span></div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <button type="reset" class="btn btn-outline-secondary px-4">Limpar itens</button>
                    <button
                        type="submit"
                        class="btn btn-success px-4 vet-billing-submit disabled"
                        disabled
                        data-default-text="{{ $defaultButtonText }}"
                        data-loading-text="{{ $loadingButtonText }}"
                    >
                        <i class="ri-checkbox-circle-line"></i>
                        <span class="vet-billing-submit-text">{{ $defaultButtonText }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-none">
        <table>
            <tbody id="vet-billing-service-template">
                @include('petshop.vet.atendimentos.partials.billing.service-row')
            </tbody>
        </table>
        <table>
            <tbody id="vet-billing-product-template">
                @include('petshop.vet.atendimentos.partials.billing.product-row')
            </tbody>
        </table>
    </div>
@endsection

@section('js')
    <script src="{{ asset('js/vet/atendimento-billing.js') }}"></script>
@endsection