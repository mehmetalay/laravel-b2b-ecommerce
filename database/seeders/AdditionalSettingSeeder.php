<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdditionalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $additionalSettings = [
            [
                'id' => 1,
                'decimal' => 2,
                'admin_password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'change-me')),
                'purchase_limit_minimum' => 1,
                'purchase_limit_maximum' => null,
                'site_status' => 1,
                'coming_soon_title' => 'Sitemizde yazılım güncellemesi yapılmaktadır.',
                'coming_soon_text' => 'Yakında açılacaktır. Anlayışınız için teşekkür ederiz.',
                'min_stock_quantity' => 1,
                'max_stock_quantity' => null,
                'display_of_out_of_stock_products' => 1,
                'show_stock' => 1,
                'product_record_per_page' => 96,
                'maximum_stock_number_display_user' => null,
                'maximum_stock_number_display_plasiyer' => null,
                'order_emails' => 'test@siteadi.com',
                'payment_emails' => 'test@siteadi.com',
                'dealer_application_mails' => 'test@siteadi.com',
                'use_contract_approval' => 1,
                'default_company_id' => 1,
                'cart_item_note_visibility' => 1,
                'payment_plan_selection' => 0,
                'payment_plan_required' => 0,
                'payment_type_selection' => 0,
                'payment_type_required' => 0,
                'delivery_type_selection' => 1,
                'delivery_type_required' => 1,
                'allow_over_order' => 1,
                'is_critical_stock_enabled' => 1,
                'critical_stock_threshold' => 5,
                'is_order_confirmation' => 1,
                'default_product_view_type' => 'grid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('additional_settings')->upsert(
            $additionalSettings,
            [
                'id',
            ],
            [
                'decimal',
                'admin_password',
                'purchase_limit_minimum',
                'purchase_limit_maximum',
                'site_status',
                'coming_soon_title',
                'coming_soon_text',
                'min_stock_quantity',
                'max_stock_quantity',
                'display_of_out_of_stock_products',
                'show_stock',
                'product_record_per_page',
                'maximum_stock_number_display_user',
                'maximum_stock_number_display_plasiyer',
                'order_emails',
                'payment_emails',
                'dealer_application_mails',
                'use_contract_approval',
                'default_company_id',
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
                'updated_at',
            ]
        );
    }
}
