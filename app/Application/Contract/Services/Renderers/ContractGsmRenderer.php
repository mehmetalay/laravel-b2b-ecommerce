<?php

namespace App\Application\Contract\Services\Renderers;

use Illuminate\Support\Collection;

class ContractGsmRenderer
{
    public function render(Collection $gsms): string
    {
        $items = $gsms->sortBy('sort_order')->pluck('gsm')->filter(fn ($value) => trim((string) $value) !== '')->values();
        if ($items->isEmpty()) {
            return '';
        }

        $html = '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="6" cellspacing="0"><thead><tr><th>GSM NUMARALARI</th></tr></thead><tbody>';
        foreach ($items as $value) {
            $html .= '<tr><td>' . e(format_phone_number($value)) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        return str_contains($html, '<tbody></tbody>') ? '' : $html;
    }
}
