@extends('admin.layouts.app')

@section('title', 'POS Yönetimi')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => route('admin.settings.pos-managements.index'), 'label' => 'Pos Yönetimi'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn" form="pos-management-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>POS Yönetimi</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div id="toggleAccordionWithIcon" class="basic-accordion-icon">
                            @foreach ($bankIntegrations as $bankIntegration)
                                <div class="card">
                                    <div class="card-header" id="basicAccordionIconheading-{{ $bankIntegration->id }}">
                                        <section class="mb-0 mt-0">
                                            <div role="menu" class="collapsed" data-toggle="collapse" data-target="#basicAccordionIcon-{{ $bankIntegration->id }}" aria-expanded="true" aria-controls="basicAccordionIcon-{{ $bankIntegration->id }}">
                                                {{ $bankIntegration->full_name }} <div class="icons"><i class="las la-angle-down"></i></div>
                                            </div>
                                        </section>
                                    </div>
                                    <div id="basicAccordionIcon-{{ $bankIntegration->id }}" class="collapse" aria-labelledby="basicAccordionIconheading-{{ $bankIntegration->id }}" data-parent="#toggleAccordionWithIcon">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <x-backend.input id="bank_name[{{ $bankIntegration->id }}]" label="Banka Adı" type="text" :value="$bankIntegration->name" :required="true" form="pos-management-form" />
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <x-backend.input id="erp_bank_code[{{ $bankIntegration->id }}]" label="ERP Banka Kodu" type="text" :value="$bankIntegration->erp_bank_code" :required="true" form="pos-management-form" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-12 col-md-3">
                                                    <div class="form-group row">
                                                        <div class="col-3">
                                                            <span class="switch align-items-start">
                                                                <label>
                                                                    <input type="checkbox" name="bank_status[{{ $bankIntegration->id }}]" {{ $bankIntegration->status ? 'checked' : '' }} form="pos-management-form">
                                                                    <span></span>
                                                                </label>
                                                            </span>
                                                        </div>
                                                        <label class="col-9 col-form-label" id="status-label-text"> {{ $bankIntegration->status ? 'Aktif' : 'Pasif' }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <h6>Taksit Oranları</h6>
                                                <div class="row">
                                                    @foreach ($bankIntegration->allInstallments as $installment)
                                                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 my-1">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $installment->installment }}</span>
                                                                </div>
                                                                <input type="text" class="form-control" name="commission_rate[{{ $installment->id }}]" value="{{ $installment->commission_rate }}" {{ $installment->installment == 1 ? 'disabled' : '' }} form="pos-management-form">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">
                                                                        <label class="checkbox checkbox-inline checkbox-primary mb-0">
                                                                            <input type="checkbox" name="status[{{ $installment->id }}]" value="{{ $installment->status ? 'true' : 'false' }}" {{ $installment->status ? 'checked' : '' }} {{ $installment->installment == 1 ? 'disabled' : '' }} form="pos-management-form">
                                                                            <span></span>
                                                                        </label>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form action="{{ route('admin.settings.pos-managements.update', [1]) }}" method="post" data-ajax-form id="pos-management-form">
        @csrf
        @method('PATCH')
    </form>
@endsection
