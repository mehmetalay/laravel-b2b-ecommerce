@extends('backend.layouts.app')

@section('title', 'Ödeme Raporları')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ödeme Raporları']
        ]">
        <li class="nav-item"></li>
    </x-backend.breadcrumb>

    @php
        $salesmanOptions = $salesmans->map(fn ($salesman) => [
            'value' => (string) $salesman->current_account_id,
            'label' => (string) $salesman->name,
        ])->values();

        $bankOptions = $bankIntegrations->map(fn ($bankIntegration) => [
            'value' => (string) $bankIntegration->id,
            'label' => (string) $bankIntegration->full_name,
        ])->values();

        $vuePaymentTableProps = [
            'endpoint' => '/admin/api/payments',
            'salesmanOptions' => $salesmanOptions ?? [],
            'bankOptions' => $bankOptions ?? [],
        ];
    @endphp

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div
                    data-vue="payments-index"
                    data-props='@json($vuePaymentTableProps)'
                ></div>
            </div>
        </div>
    </div>
@endsection
