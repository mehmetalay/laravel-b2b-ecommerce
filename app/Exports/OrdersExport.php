<?php

namespace App\Exports;

use App\Models\Order;
use App\Services\Exports\OrderExportQueryService;
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

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, WithEvents, ShouldQueue
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
        /** @var OrderExportQueryService $service */
        $service = app(OrderExportQueryService::class);

        return $service->build($this->filters, $this->selectedIds)->orderBy('id', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'KULLANICI_TURU',
            'PLASIYER',
            'BAYI',
            'ALT_BAYI',
            'TOPLAM_TUTAR',
            'PARA_BIRIMI',
            'SIPARIS_TARIHI',
            'MAIL_DURUMU',
            'SIPARIS_DURUMU',
            'ERP_DURUMU',
        ];
    }

    /**
     * @param Order $row
     */
    public function map($row): array
    {
        return [
            $row->id,
            $this->resolveUserTypeLabel((string) $row->creator_type),
            $row->plasiyer?->name ?? '-',
            $row->user?->name ?? '-',
            $row->subDealer?->name ?? '-',
            round((float) ($row->total_price ?? 0), 2),
            (string) ($row->currency ?? ''),
            (string) ($row->formatted_created_at ?? ''),
            (int) ($row->email_sent ?? 0) === 1 ? 'Gonderildi' : 'Gonderilmedi',
            $row->orderStatus?->name ?? '-',
            $this->resolveErpStatusLabel((string) ($row->erp_status ?? '')),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_00,
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

    private function resolveUserTypeLabel(string $value): string
    {
        return match ($value) {
            'salesman' => 'Plasiyer',
            'dealer' => 'Bayi',
            'subdealer' => 'Alt Bayi',
            default => '-',
        };
    }

    private function resolveErpStatusLabel(string $value): string
    {
        return match ($value) {
            'processing' => 'Isleniyor',
            'pending' => 'Beklemede',
            'sent' => 'Gonderildi',
            'failed' => 'Gonderilmedi',
            default => '-',
        };
    }
}
