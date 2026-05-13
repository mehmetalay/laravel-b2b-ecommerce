<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themeSettings = [
            [
                'id' => 1,
                'copyright' => '© 2026 FIRMA - Tüm Hakları Saklıdır.',
                'copyright_en' => '© 2026 COMPANY - All rights reserved.',
                'footer_about_us_text' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
                'footer_about_us_text_en' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
                'footer_social_title' => 'Bizi takip edin:',
                'facebook' => 'https://www.facebook.com/',
                'instagram' => 'https://www.instagram.com/',
                'twitter' => 'https://x.com/home',
                'pinterest' => 'https://tr.pinterest.com/',
                'youtube' => 'https://www.youtube.com/?app=desktop&hl=tr',
                'linkedin' => 'linkedin.com/home?originalSubdomain=tr',
                'whatsapp' => '5000000000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('theme_settings')->upsert(
            $themeSettings,
            [
                'id',
            ],
            [
                'copyright',
                'copyright_en',
                'footer_about_us_text',
                'footer_about_us_text_en',
                'footer_social_title',
                'facebook',
                'instagram',
                'twitter',
                'pinterest',
                'youtube',
                'linkedin',
                'whatsapp',
                'updated_at',
            ]
        );
    }
}
