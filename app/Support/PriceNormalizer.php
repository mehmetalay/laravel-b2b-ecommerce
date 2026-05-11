<?php

namespace App\Support;

class PriceNormalizer
{
    public static function normalize($value, bool $zeroAsNull = false): ?float
    {
        if ($value === null) {
            return null;
        }

        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }

        $s = str_replace(["\xC2\xA0", ' '], '', $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^\d\.\-]/', '', $s);

        if (substr_count($s, '.') > 1) {
            $lastPos = strrpos($s, '.');
            $intPart = str_replace('.', '', substr($s, 0, $lastPos));
            $decPart = substr($s, $lastPos + 1);
            $s = $intPart . '.' . $decPart;
        }

        if ($s === '' || !is_numeric($s)) {
            return null;
        }

        $f = (float) $s;

        if ($zeroAsNull && $f == 0.0) {
            return null;
        }

        return $f;
    }
}

