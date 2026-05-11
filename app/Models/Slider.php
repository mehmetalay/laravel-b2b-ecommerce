<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slider extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'status',
        'image_desktop_tr',
        'image_desktop_en',
        'image_tablet_tr',
        'image_tablet_en',
        'image_mobile_tr',
        'image_mobile_en',
        'link',
        'target_blank',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'target_blank' => 'boolean',
    ];

    public function image(string $device = 'desktop')
    {
        $device = in_array($device, ['desktop', 'tablet', 'mobile'])
            ? $device
            : 'desktop';

        $locale = substr(app()->getLocale(), 0, 2);
        $locale = in_array($locale, ['tr', 'en']) ? $locale : 'tr';

        $map = [
            'desktop' => [
                'tr' => $this->image_desktop_tr,
                'en' => $this->image_desktop_en,
            ],
            'tablet' => [
                'tr' => $this->image_tablet_tr,
                'en' => $this->image_tablet_en,
            ],
            'mobile' => [
                'tr' => $this->image_mobile_tr,
                'en' => $this->image_mobile_en,
            ],
        ];

        // 1️⃣ locale + device
        if (!empty($map[$device][$locale])) {
            return $map[$device][$locale];
        }

        // 2️⃣ device var ama locale yok → TR
        if (!empty($map[$device]['tr'])) {
            return $map[$device]['tr'];
        }

        // 3️⃣ device yok → desktop locale
        if (!empty($map['desktop'][$locale])) {
            return $map['desktop'][$locale];
        }

        // 4️⃣ son fallback
        return $this->image_desktop_tr;
    }

    public function imageWithDevice(string $device = 'desktop'): array
    {
        $device = in_array($device, ['desktop', 'tablet', 'mobile'])
            ? $device
            : 'desktop';

        $locale = substr(app()->getLocale(), 0, 2);
        $locale = in_array($locale, ['tr', 'en']) ? $locale : 'tr';

        $map = [
            'desktop' => [
                'tr' => $this->image_desktop_tr,
                'en' => $this->image_desktop_en,
            ],
            'tablet' => [
                'tr' => $this->image_tablet_tr,
                'en' => $this->image_tablet_en,
            ],
            'mobile' => [
                'tr' => $this->image_mobile_tr,
                'en' => $this->image_mobile_en,
            ],
        ];

        // 1️⃣ locale + device
        if (!empty($map[$device][$locale])) {
            return ['file' => $map[$device][$locale], 'device' => $device];
        }

        // 2️⃣ device var ama locale yok → TR
        if (!empty($map[$device]['tr'])) {
            return ['file' => $map[$device]['tr'], 'device' => $device];
        }

        // 3️⃣ fallback → desktop locale
        if (!empty($map['desktop'][$locale])) {
            return ['file' => $map['desktop'][$locale], 'device' => 'desktop'];
        }

        // 4️⃣ son fallback
        return ['file' => $this->image_desktop_tr, 'device' => 'desktop'];
    }

    public function getAdminImageAttribute(): ?string
    {
        return image_url(
            $this->image_desktop_tr,
            "{$this->type}.desktop"
        );
    }

    public function getTypeTextAttribute(): string
    {
        return match ($this->type) {
            'slider' => 'Anasayfa Slider',
            'payment_slider' => 'Ödeme Slider',
            'category_slider' => 'Kategori Slider',
            'campaign_slider' => 'Kampanya Slider',
            default => 'Bilinmeyen Slider',
        };
    }
}