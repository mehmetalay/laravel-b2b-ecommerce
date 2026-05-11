<?php

namespace App\Application\Contract\Services\Renderers;

use Illuminate\Support\Collection;

class ContractBankTableRenderer
{
    public function render(Collection $banks): string
    {
        $banks = $banks->sortBy('sort_order')->values();
        if ($banks->isEmpty()) {
            return '';
        }

        $html = '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="6" cellspacing="0">';
        $html .= '<thead><tr><th colspan="4">BANKA HESAP BİLGİLERİNİZ</th></tr><tr><th>BANKA</th><th>ŞUBE</th><th>HESAP NO</th><th>HESAP SAHİBİ</th></tr></thead><tbody>';

        foreach ($banks as $bank) {
            $isEmpty = empty(trim((string) $bank->bank_name))
                && empty(trim((string) $bank->branch))
                && empty(trim((string) $bank->account_no))
                && empty(trim((string) $bank->account_holder));

            if ($isEmpty) {
                continue;
            }

            $html .= '<tr><td>' . e($bank->bank_name) . '</td><td>' . e($bank->branch) . '</td><td>' . e($bank->account_no) . '</td><td>' . e($bank->account_holder) . '</td></tr>';
        }

        $html .= '</tbody></table>';

        return str_contains($html, '<tbody></tbody>') ? '' : $html;
    }
}
