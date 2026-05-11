<?php

namespace App\Exports;

use App\Models\Product;
use App\Services\Exports\ProductExportQueryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, WithEvents, ShouldQueue
{
    use Exportable;

    private array $filters;

    private ?array $selectedIds;

    public function __construct(array $filters = [], ?array $selectedIds = null)
    {
        $this->filters = $filters;
        $this->selectedIds = $selectedIds;
    }

    public function query(): Builder
    {
        /** @var ProductExportQueryService $service */
        $service = app(ProductExportQueryService::class);

        return $service->build($this->filters, $this->selectedIds)->orderBy('id', 'asc');
    }

    public function headings(): array
    {
        return ['ID', 'URUN ADI', 'KATEGORI', 'MARKA', 'FIYAT', 'STOK', 'DURUM'];
    }

    /**
     * @param Product $row
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->product_name,
            $row->category_name,
            $row->brand_name,
            $this->parsePrice($row->product_price_show),
            (int) $row->stock,
            $row->status ? 'Aktif' : 'Pasif',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0.00 [$₺-tr-TR]',
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->freezePane('A2');
            },
        ];
    }

    private function parsePrice($value): float
    {
        $parsed = strip_tags((string) $value);
        $parsed = str_replace(['₺', 'TL', ' ', "\xc2\xa0"], '', $parsed);
        $parsed = str_replace('.', '', $parsed);
        $parsed = str_replace(',', '.', $parsed);

        return (float) $parsed;
    }
}
