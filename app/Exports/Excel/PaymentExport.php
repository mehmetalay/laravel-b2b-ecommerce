<?php

namespace App\Exports\Excel;

use App\Models\Payment;
use Illuminate\Contracts\View\View;
use App\Services\CurrentAccountService;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $name;
    protected $status;
    protected $bankIntegrationId;
    protected $salesmanId;
    protected $date_from;
    protected $date_to;
    protected $source;

    public function __construct(
            $name = null,
            $status = null,
            $bankIntegrationId = null,
            $salesmanId = null,
            $date_from = null,
            $date_to = null,
            $source = null
        )
    {
        $this->name = $name;
        $this->status = $status;
        $this->bankIntegrationId = $bankIntegrationId;
        $this->salesmanId = $salesmanId;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->source = $source;
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

        $sheet->getStyle('2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5c5c5c']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->getStyle('A3:P1000')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A3:P1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('F')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
        $sheet->getStyle('I')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
    }

    public function view(): View
    {
        $userQuery = app(CurrentAccountService::class)->userQuery();

        $payments = Payment::with(['bankIntegration', 'plasiyer', 'user'])
            ->when($this->name, function ($query) {
                $query->where(function ($query) {
                    $query->whereRelation('user', 'name', 'like', "%{$this->name}%")
                          ->orWhereRelation('user', 'code', 'like', "%{$this->name}%")
                          ->orWhereRelation('user', 'email', 'like', "%{$this->name}%")
                          ->orWhere('oid', 'like', "%{$this->name}%")
                          ->orWhere('card_name', 'like', "%{$this->name}%")
                          ->orWhere('card_number', 'like', "%{$this->name}%");
                });
            })
            ->when($this->bankIntegrationId, function ($query) {
                $query->whereIn('bank_integration_id', explode(',', $this->bankIntegrationId));
            })
            ->when($this->salesmanId, function ($query) {
                $query->whereIn('plasiyer_id', explode(',', $this->salesmanId));
            })
            ->when($this->date_from, function ($query) {
                $query->where('created_at', '>=', "{$this->date_from} 00:00:00");
            })
            ->when($this->date_to, function ($query) {
                $query->where('created_at', '<=', "{$this->date_to} 23:59:59");
            })
            ->when(!$this->status, function ($query) {
                $query->whereIn('status', ['SUCCESS', 'FAILED']);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            });

        if ($this->source && in_array($this->source, ['b2b'])) {
            $payments = $payments->when(auth('web')->check() && auth('web')->user()->role === 'salesman', function ($query) use ($userQuery) {
                    $query->where('plasiyer_id', $userQuery['plasiyer_id']);
                })
                ->when(auth('web')->check() && auth('web')->user()->role === 'dealer', function ($query) use ($userQuery) {
                    $query->where('user_id', $userQuery['user_id']);
                })
                ->when(auth('subdealer')->check(), function ($query) use ($userQuery) {
                    $query->where('sub_dealer_id', $userQuery['sub_dealer_id']);
                });
        }

        $payments = $payments->orderBy('id', 'DESC')
            ->get();

        return view('exports.excel.payment', [
            'payments' => $payments
        ]);
    }
}
