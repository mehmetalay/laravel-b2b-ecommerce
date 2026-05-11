<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
        'amount_locked' => 'boolean',
        'manual_lock_bank_installment' => 'boolean',
    ];

    public $timestamps = true;

    public function plasiyer()
    {
        return $this->belongsTo(User::class, 'plasiyer_id', 'current_account_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function bankIntegration()
    {
        return $this->belongsTo(BankIntegration::class)->withTrashed();
    }

    public function manualBankIntegration()
    {
        return $this->belongsTo(BankIntegration::class, 'manual_bank_integration_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'current_account_id');
    }

    public function currentPayment()
    {
        return $this->belongsTo(Payment::class, 'current_payment_id');
    }

    public function paidPayment()
    {
        return $this->belongsTo(Payment::class, 'paid_payment_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function getFormattedPhoneNumberAttribute()
    {
        return format_phone_number($this->phone_number);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return format_date_time($this->created_at);
    }

    public function getFormattedPaymentDateAttribute()
    {
        return format_date_time($this->payment_date);
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

    public function getFormattedInstallmentAttribute()
    {
        return $this->installment == 1 ? 'TEK ÇEKİM' : "{$this->installment} TAKSİT";
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
}
