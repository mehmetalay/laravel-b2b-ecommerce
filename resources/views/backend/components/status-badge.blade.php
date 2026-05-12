@php
    $map = [
        // isset ile Plasiyer / Bayi
        'user_type' => [
            'salesman' => ['label' => 'Plasiyer', 'class' => 'badge bg-secondary'],
            'dealer' => ['label' => 'Bayi', 'class' => 'badge bg-primary'],
            'subdealer' => ['label' => 'Alt Bayi', 'class' => 'badge bg-info'],
        ],

        // 1: Aktif / 2: Pasif
        'active_passive' => [
            1 => ['label' => 'Aktif', 'class' => 'badge bg-success'],
            0 => ['label' => 'Pasif', 'class' => 'badge bg-danger'],
        ],

        // 1: Gönderildi / 0: Gönderilmedi
        'sent' => [
            1 => ['label' => 'Gönderildi', 'class' => 'badge bg-success'],
            0 => ['label' => 'Gönderilmedi', 'class' => 'badge bg-warning text-dark'],
        ],

        // 1: Serbest / 2: Taksitsiz İşlem / 3: Manuel
        'transaction_type' => [
            1 => ['label' => 'Serbest', 'class' => 'badge bg-info'],
            2 => ['label' => 'Taksitsiz İşlem', 'class' => 'badge bg-primary'],
            3 => ['label' => 'Manuel', 'class' => 'badge bg-dark'],
        ],

        // 1: Evet / 0: Hayır
        'yes_no' => [
            1 => ['label' => 'Evet', 'class' => 'badge bg-success'],
            0 => ['label' => 'Hayır', 'class' => 'badge bg-danger'],
        ],

        // 1: Ödeme Yapıldı / 2: Ödeme Bekleniyor
        'payment_status' => [
            1 => ['label' => 'Ödeme Yapıldı', 'class' => 'badge bg-success'],
            0 => ['label' => 'Ödeme Bekleniyor', 'class' => 'badge bg-warning text-dark'],
        ],

        // 1: ADMIN / 0: PLASİYER
        'role' => [
            1 => ['label' => 'Admin', 'class' => 'badge bg-dark'],
            0 => ['label' => 'Plasiyer', 'class' => 'badge bg-secondary'],
        ],

        // 0: Başarılı / 1: Başarısız
        'success_fail' => [
            'SUCCESS' => ['label' => 'Başarılı', 'class' => 'badge bg-success'],
            'FAILED' => ['label' => 'Başarısız', 'class' => 'badge bg-danger'],
        ],

        // 0: Başarılı / 1: Başarısız
        'erp_status' => [
            'processing' => ['label' => 'İşleniyor', 'class' => 'badge bg-warning text-dark'],
            'pending' => ['label' => 'Beklemede', 'class' => 'badge bg-info'],
            'sent' => ['label' => 'Gönderildi', 'class' => 'badge bg-success'],
            'failed' => ['label' => 'Gönderilmedi', 'class' => 'badge bg-danger'],
        ],
    ];

    // Eğer color/label manuel verildiyse onu kullan, yoksa map'ten al
    if ($color && $label) {
        $status = ['label' => $label, 'class' => "badge {$color}"];
    } elseif (isset($map[$type][$value])) {
        $status = $map[$type][$value];
    } else {
        $status = ['label' => $value, 'class' => 'badge bg-secondary'];
    }
@endphp

<span class="{{ $status['class'] }}">{{ $status['label'] }}</span>