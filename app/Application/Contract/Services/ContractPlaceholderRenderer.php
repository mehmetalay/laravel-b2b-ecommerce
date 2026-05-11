<?php

namespace App\Application\Contract\Services;

use App\Application\Contract\Services\Renderers\ContractBankTableRenderer;
use App\Application\Contract\Services\Renderers\ContractEmailRenderer;
use App\Application\Contract\Services\Renderers\ContractGsmRenderer;
use App\Application\Contract\Services\Renderers\ContractShipLocationRenderer;
use App\Models\Contract;

class ContractPlaceholderRenderer
{
    public function __construct(
        private ContractBankTableRenderer $contractBankTableRenderer,
        private ContractEmailRenderer $contractEmailRenderer,
        private ContractGsmRenderer $contractGsmRenderer,
        private ContractShipLocationRenderer $contractShipLocationRenderer
    ) {}

    public function render(string $content, array $actorContext, ?Contract $contract): string
    {
        $actor = $actorContext['actor'];
        $banks = $contract ? $contract->bankAccounts()->orderBy('sort_order')->get() : collect();
        $emails = $contract ? $contract->emails()->orderBy('sort_order')->get() : collect();
        $gsms = $contract ? $contract->gsms()->orderBy('sort_order')->get() : collect();
        $ships = $contract ? $contract->shipLocations()->orderBy('sort_order')->get() : collect();

        $blocks = [
            'banka_hesaplari_tablosu' => $this->contractBankTableRenderer->render($banks),
            'email_listesi' => $this->contractEmailRenderer->render($emails),
            'gsm_listesi' => $this->contractGsmRenderer->render($gsms),
            'sevk_depo_tablosu' => $this->contractShipLocationRenderer->render($ships),
        ];

        foreach ($blocks as $key => $html) {
            $content = str_replace("[{$key}]", $html, $content);
        }

        $data = [
            'musteri_fatura_unvani' => $contract ? $contract->customer_invoice_title : $actor->name,
            'musteri_fatura_adresi' => $contract
                ? $contract->customer_invoice_address
                : $actor->address . ($actor->district ? ' ' . $actor->district : '') . ($actor->province ? ' ' . $actor->province : ''),
            'telefon' => $contract ? format_phone_number($contract->phone) : $actor->phone,
            'faks' => $contract ? format_phone_number($contract->fax) : null,
            'ticaret_sicil_no' => $contract ? $contract->trade_registry_no : null,
            'vergi_dairesi' => $contract ? $contract->tax_office : null,
            'vergi_no' => $contract ? $contract->tax_number : null,
            'firma_yetkilisi' => $contract ? $contract->company_official : null,
            'mobil_telefon_numarasi' => $contract ? format_phone_number($contract->mobile_phone) : null,
            'e_posta_adresi' => $contract ? $contract->email_address : $actor->email,
            'satin_alma_yetkilisi' => $contract ? $contract->purchasing_officer : null,
            'satin_alma_mobil_telefon_numarasi' => $contract ? format_phone_number($contract->purchase_mobile_phone) : null,
            'satin_alma_e_posta_adresi' => $contract ? $contract->purchase_email_address : null,
            'odeme_yetkilisi' => $contract ? $contract->payment_authority : null,
            'odeme_yetkilisi_mobil_telefon_numarasi' => $contract ? format_phone_number($contract->payment_authority_mobile_phone) : null,
            'odeme_yetkilisi_e_posta_adresi' => $contract ? $contract->payment_authority_email_address : null,
            'muhasebe_yetkilisi' => $contract ? $contract->accounting_contact_name : null,
            'muhasebe_yetkilisi_mobil_telefon_numarasi' => $contract ? format_phone_number($contract->accounting_gsm) : null,
            'muhasebe_yetkilisi_e_posta_adresi' => $contract ? $contract->accounting_email : null,
            'aylik_odeme_gunleri' => $contract ? $contract->monthly_payment_days : null,
            'imza_tarihi' => date('d/m/Y'),
        ];

        foreach ($data as $key => $value) {
            $content = str_replace("[{$key}]", $value ?? '', $content);
        }

        return $content;
    }
}
