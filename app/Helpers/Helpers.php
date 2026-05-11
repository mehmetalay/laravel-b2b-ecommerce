<?php

use Carbon\Carbon;
use App\Helpers\{Logger, StringHelper};
use App\Services\{CurrencyService, GeneralInfoService, AdditionalSettingService};
use Illuminate\Support\Facades\{File, Cache};
use Jenssegers\Agent\Agent;

if (!function_exists('hide_category_ids')) {
    function hide_category_ids()
    {
        $hideCategoryIds = [];

        if (auth('web')->check()) {
            $hideCategoryIds = explode(',', auth('web')->user()->hide_category_ids);
        }

        return $hideCategoryIds;
    }
}

if (!function_exists('format_price')) {
    function format_price($price, $oldPrice = null, $currency, $showOldPrice = false)
    {
        $currencyService = app(CurrencyService::class);

        $formattedPrice = $currencyService->formatPrice($price, $currency);

        if ($showOldPrice) {
            $formattedOldPrice = $currencyService->formatPrice($oldPrice, $currency);
            return "<span class='text-theme'><div><del class='text-content'>{$formattedOldPrice}</del></div>{$formattedPrice}</span>";
        }

        return "<span class='text-theme'>{$formattedPrice}</span>";
    }
}

if (!function_exists('format_date_time')) {
    function format_date_time($dateTime, $returnParsed = false)
    {
        if ($returnParsed) {
            return Carbon::parse($dateTime);
        }

        return Carbon::parse($dateTime)->isoFormat('D MMMM YYYY HH:mm:ss');
    }
}

if (!function_exists('from_format')) {
    function from_format($date, $format = 'Y-m-d')
    {
        return Carbon::parse($date)->format($format);
    }
}

if (!function_exists('format_order_id')) {
    function format_order_id($orderId, $length = 10)
    {
        return str_pad($orderId, $length, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('normalize_phone_number')) {
    function normalize_phone_number($phoneNumber)
    {
        $phoneNumber = preg_replace('/\s+/', '', $phoneNumber);

        if (strpos($phoneNumber, '0') === 0) {
            $phoneNumber = substr($phoneNumber, 1);
        }

        if (strlen($phoneNumber) !== 10 || $phoneNumber[0] !== '5' || !ctype_digit($phoneNumber)) {
            return null;
        }

        return $phoneNumber;
    }
}

if (!function_exists('format_phone_number')) {
    function format_phone_number($number)
    {
        $number = preg_replace('/\D/', '', $number);

        if (strlen($number) !== 10) {
            return $number;
        }

        $area_code = substr($number, 0, 3);
        $middle_part = substr($number, 3, 3);
        $last_part1 = substr($number, 6, 2);
        $last_part2 = substr($number, 8, 2);

        return "(0$area_code) $middle_part $last_part1 $last_part2";
    }
}

if (!function_exists('custom_round')) {
    function custom_round($number)
    {
        $fraction = $number - floor($number);

        if ($fraction == 0.5) {
            return $number;
        } elseif ($fraction > 0.5) {
            return ceil($number);
        } else {
            return floor($number) + 0.5;
        }
    }
}

if (!function_exists('convert_price_to_text')) {
    function convert_price_to_text($price)
    {
        $ones = array('', 'bir', 'iki', 'üç', 'dört', 'beş', 'altı', 'yedi', 'sekiz', 'dokuz');
        $tens = array('', 'on', 'yirmi', 'otuz', 'kırk', 'elli', 'altmış', 'yetmiş', 'seksen', 'doksan');
        $hundreds = array('', 'yüz', 'iki yüz', 'üç yüz', 'dört yüz', 'beş yüz', 'altı yüz', 'yedi yüz', 'sekiz yüz', 'dokuz yüz');
        $thousands = array('', 'bin', 'milyon', 'milyar', 'trilyon', 'katrilyon');

        $priceParts = explode('.', $price);
        $integerPart = $priceParts[0];
        $decimalPart = isset($priceParts[1]) ? $priceParts[1] : '';

        $integerWords = '';
        if ($integerPart == '0') {
            $integerWords = 'sıfır';
        } else {
            $integerPart = str_pad($integerPart, ceil(strlen($integerPart) / 3) * 3, '0', STR_PAD_LEFT);
            $integerPart = str_split($integerPart, 3);

            $groupCount = count($integerPart);
            for ($i = 0; $i < $groupCount; $i++) {
                $group = (int)$integerPart[$i];
                if ($group != 0) {
                    $integerWords .= $hundreds[$group / 100 % 10] . ' ' . $tens[$group / 10 % 10] . ' ' . $ones[$group % 10] . ' ' . $thousands[$groupCount - $i - 1] . ' ';
                }
            }
        }

        $decimalWords = '';
        if (!empty($decimalPart)) {
            if (isset($decimalPart[0]) && $decimalPart[0] != '0') {
                $decimalWords .= $tens[$decimalPart[0]] . ' ';
            }

            if (isset($decimalPart[1]) && $decimalPart[1] != '0') {
                $decimalWords .= $ones[$decimalPart[1]] . ' ';
            }
        }

        $currencyWords = 'Türk Lirası';
        $centsWords = 'kuruş';

        $spokenPrice = trim($integerWords . ' ' . $currencyWords . ' ' . (empty($decimalWords) ? 'sıfır' : $decimalWords) . ' ' . $centsWords);

        if (strpos($spokenPrice, "bir bin") === 0) {
            $spokenPrice = substr_replace($spokenPrice, "bin", 0, strlen("bir bin"));
        }

        return $spokenPrice;
    }
}

if (!function_exists('base64_image')) {
    function base64_image($path)
    {
        $pathWithoutQuery = explode('?', $path)[0];

        $path = public_path($pathWithoutQuery);

        if (file_exists($path)) {
            return 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($path));
        }

        return null;
    }
}

if (!function_exists('logException')) {
    function logException(Throwable $e, ?string $context = null, ?bool $sendEmail = false): void
    {
        Logger::exception($e, $context, $sendEmail);
    }
}

if (!function_exists('logSession')) {
    function logSession(string $message, $data = null, string $level = 'info', ?string $channel = null): void
    {
        Logger::session($message, $data, $level, $channel);
    }
}

if (!function_exists('sanitize_error_message')) {
    function sanitize_error_message($errorMessage)
    {
        return str_replace(
            [
                '&#x11E;', '&#x11e;', '&#x130;', '&#x15E;', '&#x15e;',
                '&#x11F;', '&#x11f;', '&#x131;', '&#x15F;', '&#x15f;'
            ],
            [
                'Ğ', 'ğ', 'İ', 'Ş', 'ş',
                'ğ', 'ğ', 'ı', 'ş', 'ş'
            ],
            $errorMessage
        );
    }
}

if (!function_exists('virtual_pos_order_id')) {
    function virtual_pos_order_id($bank_integration_id)
    {
        if ($bank_integration_id === 9) {
            $prefix = 'PAY';
            $prefixLength = strlen($prefix);
            $totalLength = 20;

            $uniquePart = strtoupper(uniqid());

            $remainingLength = $totalLength - $prefixLength;
            $uniquePart = substr($uniquePart, 0, $remainingLength);

            if (strlen($uniquePart) < $remainingLength) {
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                for ($i = strlen($uniquePart); $i < $remainingLength; $i++) {
                    $uniquePart .= $characters[rand(0, strlen($characters) - 1)];
                }
            }

            return $prefix . $uniquePart;
        } else {
            return strtoupper(uniqid('PAY') . rand(0, 99999));
        }
    }
}

if (!function_exists('sanitize_phone_number')) {
    function sanitize_phone_number($number)
    {
        if (empty($number)) {
            return null;
        }

        $number = trim($number);

        return str_replace(['(', ')', '-', ' '], '', $number);
    }
}

if (!function_exists('forget_cache_keys')) {
    function forget_cache_keys($keys): void
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}

if (!function_exists('mask_card_number')) {
    function mask_card_number($number)
    {
        return substr($number, 0, 4) . ' ' . substr($number, 4, 2) . '** **** ' . substr($number, -4);
    }
}

if (!function_exists('calculate_percentage')) {
    function calculate_percentage($amount, $rate)
    {
        return $amount * ($rate / 100);
    }
}

if (!function_exists('discount_percentage')) {
    function discount_percentage($listPrice, $discountedPrice, $precision = 2)
    {
        if ($listPrice <= 0 || $discountedPrice < 0) {
            return 0;
        }

        $discount = (($listPrice - $discountedPrice) / $listPrice) * 100;

        return number_format($discount, $precision);
    }
}

if (!function_exists('create_directory')) {
    function create_directory($paths)
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            File::makeDirectory($path, 0777, true, true);
        }
    }
}

if (! function_exists('implode_json_column')) {
    function implode_json_column(?string $json, string $column): ?string
    {
        if (empty($json)) {
            return null;
        }

        $array = json_decode($json, true);

        if (empty($array) || !is_array($array)) {
            return null;
        }

        return implode(',', array_column($array, $column));
    }
}

if (!function_exists('image_url')) {
    function image_url(?string $imageName, string $type): ?string
    {
        $folders = [
            'slider' => [
                'homepage' => [
                    'desktop' => 'assets/images/slider/',
                    'tablet' => 'assets/images/slider/tablet/',
                    'mobile' => 'assets/images/slider/mobile/',
                ],
                'payment_page' => [
                    'desktop' => 'assets/images/slider/payment/',
                    'tablet' => 'assets/images/slider/payment/tablet/',
                    'mobile' => 'assets/images/slider/payment/mobile/',
                ],
                'desktop' => 'assets/images/slider/',
                'tablet' => 'assets/images/slider/tablet/',
                'mobile' => 'assets/images/slider/mobile/'
            ],
            'payment_slider' => [
                'desktop' => 'assets/images/slider/payment/',
                'tablet' => 'assets/images/slider/payment/tablet/',
                'mobile' => 'assets/images/slider/payment/mobile/',
            ],
            'category_slider' => [
                'desktop' => 'assets/images/slider/category/',
                'tablet' => 'assets/images/slider/category/tablet/',
                'mobile' => 'assets/images/slider/category/mobile/',
            ],
            'campaign_slider' => [
                'desktop' => 'assets/images/slider/campaign/',
                'tablet' => 'assets/images/slider/campaign/tablet/',
                'mobile' => 'assets/images/slider/campaign/mobile/',
            ],
            'images' => 'assets/images/',
            'payment' => 'assets/images/payment/',
            'country' => 'assets/images/country/',
            'product' => [
                'small' => 'assets/images/products/small/',
                'large' => 'assets/images/products/large/'
            ],
            'category' => [
                'desktop' => 'assets/images/categories/',
            ],
            'brand' => 'assets/images/brands/',
            'favicon' => 'assets/images/favicons/',
            'inner_page' => [
                'main' => 'assets/images/inner-page/',
                'about_us' => 'assets/images/inner-page/about-us/'
            ],
            'bank_logo' => 'assets/images/bank-logos/',
        ];

        $path = data_get($folders, $type);

        if (!$path || !$imageName) {
            return null;
        }

        $fullPath = public_path($path . $imageName);

        if (file_exists($fullPath)) {
            $version = filemtime($fullPath);
            return asset($path . $imageName) . '?v=' . $version;
        }

        return null;
    }
}

if (!function_exists('general_info')) {
    function general_info($key = null, $default = null)
    {
        $service = app(GeneralInfoService::class);

        $generalInfos = $service->getFirst();

        if (is_null($key)) {
            return $generalInfos;
        }

        return $generalInfos->{$key} ?? $default;
    }
}

if (!function_exists('additional_setting')) {
    function additional_setting($key = null, $default = null)
    {
        $service = app(AdditionalSettingService::class);

        $settings = $service->getFirst();

        if (is_null($key)) {
            return $settings;
        }

        return $settings->{$key} ?? $default;
    }
}

if (!function_exists('versioned_asset')) {
    function versioned_asset($path)
    {
        $fullPath = public_path($path);
        $version = file_exists($fullPath) ? filemtime($fullPath) : time();
        return asset($path) . '?v=' . $version;
    }
}

if (!function_exists('str_slug')) {
    function str_slug($value, $separator = '-') {
        return StringHelper::slug($value, $separator);
    }
}

if (!function_exists('str_title')) {
    function str_title($value)
    {
        return StringHelper::title($value);
    }
}

if (!function_exists('str_title_utf8')) {
    function str_title_utf8($value)
    {
        return StringHelper::titleUtf8($value);
    }
}

if (!function_exists('str_upper')) {
    function str_upper($value)
    {
        return StringHelper::upper($value);
    }
}

if (!function_exists('str_lower')) {
    function str_lower($value)
    {
        return StringHelper::lower($value);
    }
}

if (!function_exists('str_limit')) {
    function str_limit($value, $limit = 100, $end = '...')
    {
        return StringHelper::limit($value, $limit, $end);
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with($value, $end)
    {
        return StringHelper::endsWith($value, $end);
    }
}

if (!function_exists('pdf_image_base64')) {
    function pdf_image_base64(string $relativePublicPath): string
    {
        $relativePublicPath = ltrim($relativePublicPath, '/');
        $imagePath = public_path($relativePublicPath);

        if (!is_file($imagePath)) {
            return '';
        }

        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        // jpg -> jpeg mime düzeltmesi
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        $data = file_get_contents($imagePath);
        if ($data === false) {
            return '';
        }

        return "data:{$mime};base64," . base64_encode($data);
    }
}

if (!function_exists('device_type')) {
    function device_type(): string
    {
        // $agent = new Agent();//composer require jenssegers/agent

        // if ($agent->isTablet()) {
        //     return 'tablet';
        // }

        // if ($agent->isMobile()) {
        //     return 'mobile';
        // }

        // return 'desktop';
        $ua = strtolower(request()->userAgent());

        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile')) {
            return 'mobile';
        }

        return 'desktop';
    }
}

if (!function_exists('erp_limit')) {
    function erp_limit(string $text, int $limit = 80): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));

        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) : $text;
    }
}
?>
