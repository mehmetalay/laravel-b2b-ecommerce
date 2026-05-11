<?php

namespace App\Exports\Excel;

use App\Services\CartService;
use Illuminate\Contracts\View\View;
use App\Services\CurrentAccountService;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CartExport implements FromView, ShouldAutoSize, WithStyles
{
    public function getCurrentAccountService()
    {
        return app(CurrentAccountService::class);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'd21a15']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $s1 = 5;
        $s2 = 6;
        $s3 = 7;

        if ($this->getCurrentAccountService()->groupCurrencyStatus()['has_tl']) {
            $s1 += 6;
            $s2 += 6;
            $s3 += 6;
        }

        if ($this->getCurrentAccountService()->groupCurrencyStatus()['has_usd']) {
            $s1 += 6;
            $s2 += 6;
            $s3 += 6;
        }

        if ($this->getCurrentAccountService()->groupCurrencyStatus()['has_eur']) {
            $s1 += 6;
            $s2 += 6;
            $s3 += 6;
        }

        if ($this->getCurrentAccountService()->groupCurrencyStatus()['has_gbp']) {
            $s1 += 6;
            $s2 += 6;
            $s3 += 6;
        }

        $sheet->getStyle('2:' . (string) $s1)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        $sheet->getStyle((string) $s2)->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5c5c5c']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->getStyle('A' . (string) $s3 . ':K1000')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . (string) $s3 . ':K1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('D')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
    }

    public function view(): View
    {
        return view('exports.excel.cart', [
            'carts' => app(CartService::class)->carts(),
            'today_date' => format_date_time(now()),
        ]);
    }
}
