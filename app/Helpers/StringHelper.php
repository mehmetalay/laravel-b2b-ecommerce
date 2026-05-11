<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class StringHelper
{
    public static function title($value): string // Başlık formatı (Str::title)
    {
        return Str::title(Str::lower($value));
    }

    public static function titleUtf8($value): string // UTF8 destekli başlık formatı
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    public static function upper($value): string // Tam büyük harf
    {
        return Str::upper($value);
    }

    public static function lower($value): string // Tam küçük harf
    {
        return Str::lower($value);
    }

    public static function slug($value, $separator = '-'): string // Slug
    {
        return Str::slug($value, $separator);
    }

    public static function ucfirst($value): string // İlk harfi büyük yap
    {
        return ucfirst(mb_strtolower($value, 'UTF-8'));
    }

    public static function random($length = 50): string // Rastgele string
    {
        return Str::random($length);
    }

    public static function clean($value): string // Başta ve sonda boşlukları temizle
    {
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    public static function startsWith($value, $start): bool // Belirli karakterle başlat (örnek: https:// ile başlıyorsa başlatma)
    {
        return Str::startsWith($value, $start);
    }

    public static function endsWith($value, $end): bool // Belirli karakterle bitiyor mu
    {
        return Str::endsWith($value, $end);
    }

    public static function limit($value, $limit = 100, $end = '...'): string // Belirli uzunlukta truncate
    {
        return Str::limit($value, $limit, $end);
    }
}
