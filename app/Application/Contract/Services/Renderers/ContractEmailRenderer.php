<?php

namespace App\Application\Contract\Services\Renderers;

use Illuminate\Support\Collection;

class ContractEmailRenderer
{
    public function render(Collection $emails): string
    {
        $items = $emails->sortBy('sort_order')->pluck('email')->filter(fn ($value) => trim((string) $value) !== '')->values();
        if ($items->isEmpty()) {
            return '';
        }

        $html = '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="6" cellspacing="0"><thead><tr><th>ELEKTRONİK POSTA ADRESLERİ</th></tr></thead><tbody>';
        foreach ($items as $value) {
            $html .= '<tr><td>' . e($value) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        return str_contains($html, '<tbody></tbody>') ? '' : $html;
    }
}
