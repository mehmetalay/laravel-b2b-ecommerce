<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdditionalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'decimal',
        'purchase_limit_minimum',
        'purchase_limit_maximum',
        'display_of_out_of_stock_products',
        'show_stock',
        'maximum_stock_number_display_user',
        'maximum_stock_number_display_plasiyer',
        'max_stock_quantity',
        'product_record_per_page',
        'admin_password',
        'order_emails',
        'payment_emails',
        'dealer_application_mails',
        'default_company_id',
        'site_status',
        'coming_soon_title',
        'coming_soon_text',
        'use_contract_approval',
        'cart_item_note_visibility',
        'payment_plan_selection',
        'payment_plan_required',
        'payment_type_selection',
        'payment_type_required',
        'delivery_type_selection',
        'delivery_type_required',
        'allow_over_order',
        'is_critical_stock_enabled',
        'critical_stock_threshold',
        'is_order_confirmation',
        'default_product_view_type',
    ];

    protected $casts = [
        'display_of_out_of_stock_products' => 'boolean',
        'show_stock' => 'boolean',
        'site_status' => 'boolean',
        'use_contract_approval' => 'boolean',
        'cart_item_note_visibility' => 'boolean',
        'payment_plan_selection' => 'boolean',
        'payment_plan_required' => 'boolean',
        'payment_type_selection' => 'boolean',
        'payment_type_required' => 'boolean',
        'delivery_type_selection' => 'boolean',
        'delivery_type_required' => 'boolean',
        'allow_over_order' => 'boolean',
        'is_critical_stock_enabled' => 'boolean',
        'is_order_confirmation' => 'boolean',
    ];

    public $timestamps = true;
}
