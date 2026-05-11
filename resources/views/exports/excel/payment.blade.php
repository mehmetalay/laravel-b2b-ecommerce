<table>
    <thead>
        <tr>
            <th colspan="10">{{ trans('translations.exports.excel.payment.odeme_raporu') }}</th>
        </tr>
        <tr>
            <th>{{ trans('translations.exports.excel.payment.plasiyer') }}</th>
            <th>{{ trans('translations.exports.excel.payment.bayi') }}</th>
            <th>{{ trans('translations.exports.excel.payment.alt_bayi') }}</th>
            <th>{{ trans('translations.exports.excel.payment.banka_entegrasyonu') }}</th>
            <th>{{ trans('translations.exports.excel.payment.islem_no') }}</th>
            <th>{{ trans('translations.exports.excel.payment.girilen_tutar') }}</th>
            <th>{{ trans('translations.exports.excel.payment.taksit') }}</th>
            <th>{{ trans('translations.exports.excel.payment.komisyon_orani') }}</th>
            <th>{{ trans('translations.exports.excel.payment.komisyon_tutari') }}</th>
            <th>{{ trans('translations.exports.excel.payment.tahsil_edilen_tutar') }}</th>
            <th>{{ trans('translations.exports.excel.payment.kart_sahibi_adi_soyadi') }}</th>
            <th>{{ trans('translations.exports.excel.payment.kart_numarasi') }}</th>
            <th>{{ trans('translations.exports.excel.payment.telefon_numarasi') }}</th>
            <th>{{ trans('translations.exports.excel.payment.aciklama') }}</th>
            <th>{{ trans('translations.exports.excel.payment.odeme_durumu') }}</th>
            <th>{{ trans('translations.exports.excel.payment.odeme_tarihi') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->salesman_name }}</td>
                <td>{{ $payment->dealer_name }}</td>
                <td>{{ $payment->sub_dealer_name }}</td>
                <td>{{ $payment->bank_integration_name }}</td>
                <td>{{ $payment->oid }}</td>
                <td>{{ $payment->formatted_entered_amount }}</td>
                <td>{{ $payment->installment }}</td>
                <td>{{ $payment->commission_rate }}</td>
                <td>{{ $payment->formatted_commission_amount }}</td>
                <td>{{ $payment->formatted_amount_paid }}</td>
                <td>{{ $payment->card_name }}</td>
                <td>{{ $payment->card_number }}</td>
                <td>{{ $payment->formatted_phone_number }}</td>
                <td>{{ $payment->explanation }}</td>
                <td>{{ $payment->status_name }}</td>
                <td>{{ $payment->formatted_completed_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
