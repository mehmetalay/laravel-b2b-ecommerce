@if ($paymentLink->transaction_type == 1 || $paymentLink->transaction_type == 3)
    @if ($bankIntegration->message)
        <div class="col-12">
            <span class="text-danger"><strong>{{ $bankIntegration->message }}</strong></span>
        </div>
    @endif
    @foreach ($bankIntegration->installments as $installment)
        @php
            $total_amount = ($amount * ($installment->commission_rate / 100)) + $amount;
            $total_installment = $installment->installment + $installment->plus_installment;
        @endphp
        <div class="col-xl-4 col-md-6 col-12">
            <div class="number-of-installment-box">
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="installment_id" id="{{ $installment->id }}" value="{{ $installment->id }}" {{ $paymentLink->transaction_type == 1 && $installment->installment == 1 ? 'checked' : '' }} {{ $paymentLink->transaction_type === 3 ? (($paymentLink->manual_installment != $installment->installment) ? ($paymentLink->manual_lock_bank_installment ? 'disabled' : '') : 'checked') : '' }}>
                    </div>
                    <label class="number-of-installment-detail" for="{{ $installment->id }}">
                        <ul>
                            <li>
                                <h4 class="fw-500">
                                    {{ $installment->installment == 1 ? trans('translations.payment.installment_list.tek_cekim') : $installment->installment . ' ' . trans('translations.payment.installment_list.taksit') }}
                                    {!! $installment->plus_installment != 0 ? '<span>(+' . $installment->plus_installment . ' ' . trans('translations.payment.installment_list.taksit') . ')</span>' : '' !!}
                                </h4>
                            </li>
                            <li>
                                <h6 class="text-content"><span class="text-title">{{ number_format($total_amount, 2) }}</span> TL</h6>
                            </li>
                            <li>
                                <p class="text-content mb-0"><span class="text-title">{{ $total_installment . ' x ' . number_format($total_amount / $total_installment, 2) }}</span> TL</p>
                            </li>
                        </ul>
                    </label>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="col-xl-4 col-md-6 col-12">
        <div class="number-of-installment-box">
            <div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="installment_id" id="1" value="{{ $bankIntegration->oneInstallment->id }}" checked>
                </div>
                <label class="number-of-installment-detail" for="1">
                    <ul>
                        <li>
                            <h4 class="fw-500">{{ trans('translations.payment.installment_list.tek_cekim') }}</h4>
                        </li>
                        <li>
                            <h6 class="text-content"><span class="text-title">{{ number_format($amount, 2) }}</span> TL</h6>
                        </li>
                        <li>
                            <p class="text-content mb-0"><span class="text-title">{{ '1 x ' . number_format($amount, 2) }}</span> TL</p>
                        </li>
                    </ul>
                </label>
            </div>
        </div>
    </div>
@endif
