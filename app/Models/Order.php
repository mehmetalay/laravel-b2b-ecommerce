<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public $timestamps = true;

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function plasiyer()
    {
        return $this->belongsTo(User::class, 'plasiyer_id', 'current_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'current_account_id');
    }

    public function subDealer()
    {
        return $this->belongsTo(SubDealer::class);
    }

    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id', 'row_id');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id', 'row_id');
    }

    public function cargoCompany()
    {
        return $this->belongsTo(CargoCompany::class);
    }

    public function getSalesmanNameAttribute()
    {
        return isset($this->plasiyer) ? $this->plasiyer->name : '-';
    }

    public function getSalesmanCodeAttribute()
    {
        return isset($this->plasiyer) ? $this->plasiyer->code : '-';
    }

    public function getDealerNameAttribute()
    {
        return $this->user->name;
    }

    public function getDealerMailAttribute()
    {
        return $this->user->email;
    }

    public function getSubDealerNameAttribute()
    {
        return $this->subDealer->name;
    }

    public function getFormattedTotalProductPriceAttribute()
    {
        return $this->formatPrice($this->total_product_price);
    }

    public function getFormattedCartDiscount1Attribute()
    {
        return $this->formatPrice($this->cart_discount_1);
    }

    public function getFormattedCartDiscount2Attribute()
    {
        return $this->formatPrice($this->cart_discount_2);
    }

    public function getFormattedTotalCartDiscountAttribute()
    {
        return $this->formatPrice($this->cart_discount_1 + $this->cart_discount_2);
    }

    public function getTotalAmountAttribute()
    {
        return $this->formatPrice($this->total_price);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return format_date_time($this->created_at);
    }

    public function getFormattedUsdExchangeRateAttribute()
    {
        return number_format($this->usd_exchange_rate, 6);
    }

    public function getCampaignStatusAttribute()
    {
        return $this->has_campaign ? 'Kampanyalı' : 'Kampanyasız';
    }

    public function getPaymentPlanNameAttribute()
    {
        return $this->paymentPlan ? $this->paymentPlan->definition : '-';
    }

    public function getPaymentTypeNameAttribute()
    {
        return match ($this->payment_type) {
            'cash' => 'Nakit',
            'credit' => 'Kredi Kartı',
            'term' => 'Vadeli',
            default => ucfirst($this->payment_type),
        };
    }

    public function getDeliveryTypeNameAttribute()
    {
        return match ($this->delivery_type) {
            'cargo' => 'Kargo',
            'warehouse_pickup' => 'Depodan Teslim',
            'warehouse' => 'Ambar',
            default => ucfirst($this->delivery_type),
        };
    }

    public function getTotalPriceUsdAttribute()
    {
        if ($this->currency === 'TL' && $this->usd_exchange_rate) {
            return $this->formatPrice($this->total_price / $this->usd_exchange_rate);
        }

        if ($this->currency === 'USD') {
            return $this->formatPrice($this->total_price);
        }

        return null;
    }

    protected function formatPrice($amount)
    {
        if (auth('subdealer')->check() && !auth('subdealer')->user()->can_view_prices) {
            $amount = 0;
        }

        return app(CurrencyService::class)->formatPrice($amount, $this->currency);
    }

    public function getDeliveryTypeLabelAttribute()
    {
        return match ($this->delivery_type) {
            'cargo' => 'Kargo',
            'warehouse_pickup' => 'Depodan Teslim',
            'warehouse' => 'Ambar',
            default => ucfirst($this->delivery_type),
        };
    }

    public function getShippingAddressDisplayAttribute()
    {
        return $this->shipping_address ?? '-';
    }

    public function getDeliverySummaryAttribute(): array
    {
        $data = [
            'delivery_type' => $this->delivery_type,
            'items' => [],
        ];

        switch ($this->delivery_type) {

            case 'Kargo':
                $data['items'][] = [
                    'label' => 'Kargo Firması',
                    'value' => optional($this->cargoCompany)->name ?? '-',
                ];

                if ($this->shipping_address_snapshot) {
                    $addr = json_decode($this->shipping_address_snapshot, true);

                    $data['items'][] = [
                        'label' => 'Firma',
                        'value' => $addr['company_name'] ?? '-',
                    ];

                    $data['items'][] = [
                        'label' => 'Vergi',
                        'value' => trim(($addr['tax_office'] ?? '') . ' / ' . ($addr['tax_number'] ?? '')),
                    ];

                    $data['items'][] = [
                        'label' => 'Adres',
                        'value' =>
                            ($addr['address'] ?? '') . '<br>' .
                            ($addr['city'] ?? '') . ' / ' .
                            ($addr['district'] ?? '') . ' / ' .
                            ($addr['neighborhood'] ?? ''),
                    ];
                }
                break;

            case 'Ambar':
                $data['items'][] = [
                    'label' => 'Ambar Adı',
                    'value' => $this->warehouse_name,
                ];
                break;

            case 'Depo Teslim':
                $data['items'][] = [
                    'label' => 'Teslim Alacak Kişi',
                    'value' => $this->pickup_person,
                ];
                break;

            case 'Transit Sevk':
                if ($this->transit_note) {
                    $data['items'][] = [
                        'label' => 'Transit Sevk Bilgisi',
                        'value' => $this->transit_note,
                    ];
                }

                if ($this->shipping_address_snapshot) {
                    $addr = json_decode($this->shipping_address_snapshot, true);

                    $data['items'][] = [
                        'label' => 'Sevk Adresi',
                        'value' =>
                            ($addr['address'] ?? '') . '<br>' .
                            ($addr['city'] ?? '') . ' / ' .
                            ($addr['district'] ?? ''),
                    ];
                }
                break;
        }

        return $data;
    }

    // ETA

    public function getEtaOrderNumberAttribute()
    {
        return 'OR-' . str_pad($this->id, 10, '0', STR_PAD_LEFT);
    }

    public function getEtaCreatedAtAttribute()
    {
        return from_format($this->completed_at, 'Y-m-d') . 'T00:00:00';
    }

    public function getEtaCreatedTimeAttribute()
    {
        return from_format($this->created_at, 'H:i');
    }

    public function getEtaEmptyDateAttribute()
    {
        return '1900-01-01T00:00:00';
    }

    public function getEtaPaymentTypeAttribute()
    {
        return match ($this->payment_type) {
            'cash' => 'NAKIT',
            'credit' => 'KREDI KARTI',
            'term' => 'VADELI',
            default => ucfirst($this->payment_type),
        };
    }

    public function getEtaCurrencyCodeAttribute()
    {
        return match ($this->currency) {
            'TL' => '',
            'USD' => 'USD',
            'EUR' => 'EURO',
            'GBP' => 'GBP',
            default => '',
        };
    }

    public function getEtaCurrencyTypeAttribute()
    {
        return match ($this->currency) {
            'TL' => '',
            'USD' => 'USD',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
            default => '',
        };
    }

    public function getEtaExchangeRateAttribute()
    {
        return match ($this->currency) {
            'USD' => $this->usd_exchange_rate,
            'EUR' => $this->eur_exchange_rate,
            'GBP' => $this->gbp_exchange_rate,
            default => 1,
        };
    }

    public function getEtaAccountNameAttribute()
    {
        return $this->user->name;
    }

    public function getEtaAccountCodeAttribute()
    {
        return str_replace('.', ' ', $this->user->code);
    }

    public function getEtaAdress1Attribute()
    {
        return $this->user->address_1;
    }

    public function getEtaAdress2Attribute()
    {
        return $this->user->address_2;
    }

    public function getEtaAdress3Attribute()
    {
        return $this->user->address_3;
    }

    public function getEtaPostalCodeAttribute()
    {
        return $this->user->postal_code;
    }

    public function getEtaProvinceAttribute()
    {
        return $this->user->province;
    }

    public function getEtaDistrictAttribute()
    {
        return $this->user->district;
    }

    public function getEtaTaxOfficeAttribute()
    {
        return $this->user->tax_office;
    }

    public function getEtaTaxNumberAttribute()
    {
        return $this->user->tax_number;
    }

    public function getEtaIdentityNumberAttribute()
    {
        return $this->user->identity_number;
    }

    public function getEtaDescription1Attribute(): string
    {
        $text = '';

        switch ($this->delivery_type) {

            case 'Kargo':
                $text = 'Kargo';
                if ($this->cargoCompany) {
                    $text .= ' - ' . $this->cargoCompany->name;
                }
                break;

            case 'Ambar':
                $text = 'Ambar';
                if ($this->warehouse_name) {
                    $text .= ' - ' . $this->warehouse_name;
                }
                break;

            case 'Depo Teslim':
                $text = 'Depo Teslim';
                if ($this->pickup_person) {
                    $text .= ' - ' . $this->pickup_person;
                }
                break;

            case 'Transit Sevk':
                $text = 'Transit Sevk';
                if ($this->transit_note) {
                    $text .= ' - ' . $this->transit_note;
                }
                break;
        }

        return erp_limit($text);
    }

    public function getEtaDescription2Attribute(): string
    {
        if (!$this->shipping_address_snapshot) {
            return '';
        }

        $addr = json_decode($this->shipping_address_snapshot, true);
        if (!$addr) {
            return '';
        }

        $parts = array_filter([
            $addr['address'] ?? null,
            trim(
                ($addr['district'] ?? '') .
                (($addr['city'] ?? '') ? '/' . $addr['city'] : '')
            ),
        ]);

        $text = implode(' ', $parts);

        return erp_limit($text);
    }

    public function getEtaDescription3Attribute()
    {
        if (!$this->explanation) {
            return '';
        }

        return erp_limit($this->explanation);
    }

    public function getEtaTransactionNumberAttribute()
    {
        return "ISLEM-{$this->id}";
    }

    public function getEtaDocumentNumberAttribute()
    {
        return "EVRAK-{$this->id}";
    }

    public function getEtaSpNumberAttribute()
    {
        return "SP-0000{$this->id}";
    }

    public function getEtaSaNumberAttribute()
    {
        return "SA-0000{$this->id}";
    }

    public function getEtaStNumberAttribute()
    {
        return "ST-0000{$this->id}";
    }

    public function getEtaDeliveryTypeNameAttribute()
    {
        return match ($this->delivery_type) {
            'cargo' => 'Kargo',
            'warehouse_pickup' => 'Depodan Teslim',
            'warehouse' => 'Ambar',
            default => ucfirst($this->delivery_type),
        };
    }
}
