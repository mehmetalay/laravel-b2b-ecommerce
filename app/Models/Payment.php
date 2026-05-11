<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = true;

    public function getSalesmanNameAttribute()
    {
        return $this->plasiyer ? $this->plasiyer->name : '-';
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
        return $this->subDealer ? $this->subDealer->name : '-';
    }

    public function getBankIntegrationNameAttribute()
    {
        return $this->bankIntegration->full_name;
    }

    public function getStatusNameAttribute()
    {
        $status = strtolower((string) $this->status);

        return match ($status) {
            'success' => 'Başarılı',
            'failed' => 'Başarısız',
            'refunded' => 'İade Edildi',
            default => 'Bekleyen',
        };
    }

    public function getFormattedPhoneNumberAttribute()
    {
        return format_phone_number($this->phone_number);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return format_date_time($this->created_at);
    }

    public function getFormattedCompletedAtAttribute()
    {
        return format_date_time($this->completed_at);
    }

    public function getFormattedAmountPaidAttribute()
    {
        return number_format($this->amount_paid, 2);
    }

    public function getMonthlyPaymentAmountAttribute()
    {
        return $this->amount_paid / $this->installment;
    }

    public function getFormattedMonthlyPaymentAmountAttribute()
    {
        return number_format($this->amount_paid / $this->installment, 2);
    }

    public function getFormattedEnteredAmountAttribute()
    {
        return number_format($this->entered_amount, 2);
    }

    public function getFormattedCommissionAmountAttribute()
    {
        return number_format($this->commission_amount, 2);
    }

    public function getFormattedInstallmentAttribute()
    {
        return $this->installment == 1 ? 'TEK ÇEKİM' : "{$this->installment} TAKSİT";
    }

    public function bankIntegration()
    {
        return $this->belongsTo(BankIntegration::class)->withTrashed();
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

    public function paymentLink()
    {
        return $this->belongsTo(PaymentLink::class, 'payment_link_id');
    }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    public function getAmountPaidUsdAttribute()
    {
        if ($this->usd_exchange_rate && $this->amount_paid) {
            return number_format($this->amount_paid / $this->usd_exchange_rate, 2);
        }

        return null;
    }

    public function getUsdRateInfoAttribute()
    {
        return number_format($this->usd_exchange_rate, 6) ?: null;
    }

    public function getActionTypeAttribute()
    {
        if (!is_null($this->refund_status)) {
            return null;
        }

        $tz = config('app.timezone') ?: 'UTC';
        $created = $this->created_at ? $this->created_at->copy()->setTimezone($tz) : null;
        $todayStart = Carbon::now($tz)->startOfDay();
        $todayEnd = (clone $todayStart)->endOfDay();

        if ($created && $created->gte($todayStart) && $created->lte($todayEnd)) {
            return 'cancel';
        }

        if ($created && $created->lt($todayStart)) {
            return 'refund';
        }

        return null;
    }

    public function getRefundStatusNameAttribute()
    {
        return $this->refund_status == 'cancelled' ? 'İptal Edildi' : ($this->refund_status == 'refunded' ? 'İade Edildi' : '');
    }

    public function getFormattedRefundDateAttribute()
    {
        return format_date_time($this->refund_date);
    }

    // ETA

    public function getEtaCreatedAtAttribute()
    {
        return from_format($this->completed_at, 'Y-m-d') . 'T00:00:00';
    }

    public function getEtaYearAttribute()
    {
        return (int) from_format($this->completed_at, 'Y');
    }

    public function getEtaMonthAttribute()
    {
        return (int) from_format($this->completed_at, 'm');
    }

    public function getEtaCreatedTimeAttribute()
    {
        return from_format($this->completed_at, 'H:i');
    }

    public function getEtaEmptyDateAttribute()
    {
        return '1900-01-01T00:00:00';
    }

    public function getEtaPaymentNumberAttribute()
    {
        return 'PY-' . str_pad($this->id, 10, '0', STR_PAD_LEFT);
    }

    public function getEtaBankCodeAttribute()
    {
        return $this->bankIntegration->erp_bank_code;
    }

    public function getEtaBankNameAttribute()
    {
        return $this->bankIntegration->name;
    }

    public function getEtaInstallmentTypeAttribute()
    {
        return $this->installment > 1 ? "{$this->installment} Taksit" : "Tek Çekim";
    }

    public function getEtaCardHolderInfoAttribute()
    {
        return sprintf(
            "%s, %s, %s, %s",
            str_limit($this->card_name, 15),
            $this->card_number,
            $this->oid,
            $this->phone_number
        );
    }

    public function getEtaCardHolderInfo2Attribute()
    {
        return sprintf(
            "%s, %s",
            str_limit($this->card_name, 15),
            $this->eta_installment_type
        );
    }

    public function getEtaCardHolderInfo3Attribute()
    {
        return sprintf(
            "%s, %s",
            $this->eta_bank_name,
            $this->card_number
        );
    }

    public function getEtaCardHolderInfo4Attribute()
    {
        return sprintf(
            "%s, %s",
            'Sanal Pos Tahsilatı',
            $this->ip_address
        );
    }

    public function getEtaCardHolderInfo5Attribute()
    {
        return sprintf(
            "%s, %s",
            str_limit($this->card_name, 15),
            $this->eta_installment_type
        );
    }

    public function getEtaWithdrawalAmountAttribute()
    {
        return floatval($this->entered_amount);
    }

    public function getEtaAccountNameAttribute()
    {
        return $this->user->name;
    }

    public function getEtaAccountCodeAttribute()
    {
        return str_replace('.', ' ', $this->user->code);
    }

    public function getEtaExplanationAttribute()
    {
        return $this->explanation;
    }
}
