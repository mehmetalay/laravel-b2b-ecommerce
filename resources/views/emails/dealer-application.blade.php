<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Yeni Bayi Talebi</title>
</head>
<body>
    <ul style="list-style-type: none; padding: 0px;">
        <li style="margin: 0;"><strong>Şirket Adı/Firma Ünvanı:</strong> {{ $dealer_application->company_name }}</li>
        <li style="margin: 0;"><strong>Vergi Dairesi:</strong> {{ $dealer_application->tax_office ?? '-' }}</li>
        <li style="margin: 0;"><strong>Vergi Numarası:</strong> {{ $dealer_application->tax_number ?? '-' }}</li>
        <li style="margin: 0;"><strong>Şehir:</strong> {{ $dealer_application->city }}</li>
        <li style="margin: 0;"><strong>İlçe:</strong> {{ $dealer_application->district }}</li>
        <li style="margin: 0;"><strong>Adres:</strong> {{ $dealer_application->address }}</li>
        <li style="margin: 0;"><strong>Yetkili Ad Soyad:</strong> {{ $dealer_application->authorized_name_surname }}</li>
        <li style="margin: 0;"><strong>T.C. Kimlik Numarası:</strong> {{ $dealer_application->identity_number ?? '-' }}</li>
        <li style="margin: 0;"><strong>Telefon Numarası:</strong> {!! $dealer_application->phone_number ? '<a href="tel:+90' . $dealer_application->phone_number . '">' . $dealer_application->phone_number . '</a>' : '-' !!}</li>
        <li style="margin: 0;"><strong>Cep Telefonu Numarası:</strong> <a href="tel:+90{{ $dealer_application->mobile_phone_number }}">{{ $dealer_application->mobile_phone_number }}</a></li>
        <li style="margin: 0;"><strong>Faks Numarası:</strong> {!! $dealer_application->fax_number ? '<a href="tel:+90' . $dealer_application->fax_number . '">' . $dealer_application->fax_number . '</a>' : '-' !!}</li>
        <li style="margin: 0;"><strong>E-Posta Adresi:</strong> {{ $dealer_application->email_address }}</li>
        <li style="margin: 0;"><strong>Web Adresi:</strong> {{ $dealer_application->web_address ?? '-' }}</li>
        <li style="margin: 0;"><strong>Başvuru Tarihi:</strong> {{ format_date_time($dealer_application->created_at) }}</li>
    </ul>
</body>
</html>

