<?php

namespace App\Exports;

use App\Models\Payment;
use App\Services\Exports\PaymentExportQueryService;
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

class PaymentsExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, WithEvents, ShouldQueue
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
        /** @var PaymentExportQueryService $service */
        $service = app(PaymentExportQueryService::class);

        return $service->build($this->filters, $this->selectedIds)->orderBy('id', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'PLASIYER',
            'BAYI',
            'BANKA',
            'ISLEM NO',
            'TUTAR',
            'USD TUTAR',
            'TAKSIT',
            'KOMISYON ORANI',
            'KOMISYON TUTARI',
            'KART SAHIBI',
            'KART NO',
            'ACIKLAMA',
            '3D ODEME',
            'DURUM',
            'MAIL DURUMU',
            'ERP DURUMU',
            'ISLEM TIPI',
            'TARIH',
        ];
    }

    /**
     * @param Payment $row
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->plasiyer?->name ?? '-',
            $row->subDealer?->name ?? ($row->user?->name ?? '-'),
            $row->bankIntegration?->full_name ?? '-',
            (string) ($row->oid ?? '-'),
            round((float) ($row->amount_paid ?? 0), 2),
            round((float) ($row->amount_paid_usd ?? 0), 2),
            (int) ($row->installment ?? 0),
            (int) ($row->commission_rate ?? 0),
            round((float) ($row->commission_amount ?? 0), 2),
            (string) ($row->card_name ?? ''),
            (string) ($row->card_number ?? ''),
            (string) ($row->explanation ?? ''),
            (int) ($row->option_3d_payment ?? 0) === 1 ? 'Evet' : 'Hayir',
            $this->resolveStatusLabel((string) $row->status),
            $this->resolveEmailStatusLabel((string) $row->status, (int) ($row->email_sent ?? 0)),
            $this->resolveErpStatusLabel((string) $row->erp_status),
            $this->resolveProcessTypeLabel($row->refund_status),
            (string) ($row->formatted_completed_at ?: $row->formatted_created_at),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER_00,
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

    private function resolveStatusLabel(string $value): string
    {
        return match (strtoupper($value)) {
            'SUCCESS' => 'Basarili',
            'FAILED' => 'Basarisiz',
            'REFUNDED' => 'Iade Edildi',
            default => 'Bekleyen',
        };
    }

    private function resolveEmailStatusLabel(string $status, int $emailSent): string
    {
        if (strtoupper($status) !== 'SUCCESS') {
            return '-';
        }

        return $emailSent === 1 ? 'Gonderildi' : 'Gonderilmedi';
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

    private function resolveProcessTypeLabel($refundStatus): string
    {
        if ($refundStatus === 'refunded') {
            return 'Iade';
        }

        if ($refundStatus === 'cancelled') {
            return 'Iptal';
        }

        return 'Odeme';
    }
}
