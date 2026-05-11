<?php

namespace App\Application\Contract\Services\Renderers;

use Illuminate\Support\Collection;

class ContractShipLocationRenderer
{
    public function render(Collection $ships): string
    {
        $ships = $ships->sortBy('sort_order')->values();
        if ($ships->isEmpty()) {
            return '';
        }

        $html = '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="6" cellspacing="0">';
        $html .= '<thead><tr><th colspan="7">MÜŞTERİ SEVK DEPO/MAĞAZA BİLDİRİM FORMU</th></tr><tr><th>Depo/Mağaza</th><th>Adres</th><th>İl</th><th>İlçe</th><th>Tel</th><th>Fax</th><th>Yetkili</th></tr></thead><tbody>';

        foreach ($ships as $ship) {
            $isEmpty = empty(trim((string) $ship->name))
                && empty(trim((string) $ship->address))
                && empty(trim((string) $ship->city))
                && empty(trim((string) $ship->district))
                && empty(trim((string) $ship->phone))
                && empty(trim((string) $ship->fax))
                && empty(trim((string) $ship->authorized_name));

            if ($isEmpty) {
                continue;
            }

            $html .= '<tr><td>' . e($ship->name) . '</td><td>' . e($ship->address) . '</td><td>' . e($ship->city) . '</td><td>' . e($ship->district) . '</td><td>' . e(format_phone_number($ship->phone)) . '</td><td>' . e(format_phone_number($ship->fax)) . '</td><td>' . e($ship->authorized_name) . '</td></tr>';
        }

        $html .= '</tbody></table>';

        return str_contains($html, '<tbody></tbody>') ? '' : $html;
    }
}
