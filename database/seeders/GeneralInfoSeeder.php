<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $generalInfos = [
            [
                'id' => 1,
                'company_name' => 'TEST FIRMA',
                'company_official_name' => 'TEST FIRMA',
                'company_website' => 'www.siteadi.com',
                'authorized_person' => 'TEST YETKILI',
                'company_phone_number' => '+90 500 000 00 00',
                'company_phone_number_2' => '+90 500 000 00 00',
                'company_mobile_number' => '+90 500 000 00 00',
                'fax_number' => '+90 200 000 00 00',
                'email_address' => 'testmail@siteadi.com',
                'email_address_2' => 'testmail2@siteadi.com',
                'company_full_address' => 'İstanbul/Türkiye',
                'seo_meta_title' => 'TEST FIRMA B2B',
                'seo_meta_description' => 'TEST FIRMA B2B sitesi',
                'seo_meta_keywords' => 'test firma, test, firma, b2b',
                'google_maps_link' => 'https://maps.app.goo.gl/NDcpn7XUsUP6XQSr6',
                'google_maps_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6352374.390727162!2d29.844694845385543!3d38.97548326672861!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14b0155c964f2671%3A0x40d9dbd42a625f2a!2zVMO8cmtpeWU!5e0!3m2!1str!2str!4v1778245716417!5m2!1str!2str" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('general_infos')->upsert(
            $generalInfos,
            [
                'id',
            ],
            [
                'company_name',
                'company_official_name',
                'company_website',
                'authorized_person',
                'company_phone_number',
                'company_phone_number_2',
                'company_mobile_number',
                'fax_number',
                'email_address',
                'email_address_2',
                'company_full_address',
                'seo_meta_title',
                'seo_meta_description',
                'seo_meta_keywords',
                'google_maps_link',
                'google_maps_embed',
                'updated_at',
            ]
        );
    }
}
