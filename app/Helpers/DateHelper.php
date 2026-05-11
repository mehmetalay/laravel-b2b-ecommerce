<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function nowFormatted($format = 'Y-m-d H:i:s.v'): string
    {
        return Carbon::now()->format($format);
    }

    public static function nowPlusYear(): Carbon
    {
        return Carbon::now()->addYear();
    }

    public static function nowMinusMinutes(int $minutes = 1): Carbon
    {
        return Carbon::now()->subMinutes($minutes);
    }

    public static function nowMinusDays(int $days = 1): string
    {
        return Carbon::now()->subDays($days)->toDateString();
    }

    public static function nowMinusWeek(): string
    {
        return Carbon::now()->subWeek()->toDateString();
    }

    public static function nowToDateString(): string
    {
        return Carbon::now()->toDateString();
    }

    public static function parse($date): Carbon
    {
        return Carbon::parse($date);
    }

    public static function parseIso($date): string
    {
        return Carbon::parse($date)->isoFormat('D MMMM YYYY');
    }

    public static function parseDateTime($date): string
    {
        return Carbon::parse($date)->isoFormat('D MMMM YYYY HH:mm:ss');
    }

    public static function fromFormat($date, $format = 'Y-m-d H:i:s'): Carbon
    {
        return Carbon::createFromFormat($format, $date);
    }
}
