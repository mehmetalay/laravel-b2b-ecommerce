<style>
    .installment-tag {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.60rem;
        font-weight: 500;
    }

    .no-commission {
        background-color: #d4f5e1;
        color: #2e7d32;
    }

    .commission {
        background-color: #ffe0b2;
        color: #e65100;
    }
</style>

@if ($bankIntegration->message)
    <div class="col-12">
        <span class="text-danger"><strong>{{ $bankIntegration->message }}</strong></span>
    </div>
@endif
@foreach ($bankIntegration->installments as $installment)
    @php
        $totalAmount = ($amount * ($installment->commission_rate / 100)) + $amount;
        $totalInstallment = $installment->installment;
    @endphp
    <div class="col-xl-4 col-md-6 col-12">
        <div class="number-of-installment-box">
            <div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="installment_id" id="{{ $installment->id }}" value="{{ $installment->id }}" {{ $installment->installment == 1 ? 'checked' : '' }}>
                </div>
                <label class="number-of-installment-detail" for="{{ $installment->id }}">
                    <ul>
                        <li>
                            <h4 class="fw-500">
                                {{ $installment->installment == 1 ? trans('translations.payment.installment_list.tek_cekim') : $installment->installment . ' ' . trans('translations.payment.installment_list.taksit') }}
                            </h4>
                        </li>
                        <li>
                            <h6 class="text-content"><span class="text-title">{{ number_format($totalAmount, 2) }}</span> TL</h6>
                        </li>
                        <li>
                            <p class="text-content mb-0"><span class="text-title">{{ $totalInstallment . ' x ' . number_format($totalAmount / $totalInstallment, 2) }}</span> TL</p>
                        </li>
                        <li>
                            @if ($installment->commission_rate == 0)
                                <span class="installment-tag no-commission">Vade Farksız</span>
                            @else
                                <span class="installment-tag commission">{{ number_format($totalAmount - $amount, 2) }} TL Vade Farkı</span>
                            @endif
                        </li>
                    </ul>
                </label>
            </div>
        </div>
    </div>
@endforeach