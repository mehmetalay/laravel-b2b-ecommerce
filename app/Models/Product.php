<?php

namespace App\Models;

use App\Application\Product\ProductStockDisplayService;
use App\Application\Product\DTO\ProductPriceContext;
use App\Domain\Product\ProductPriceCalculator;
use App\Services\CurrentAccountService;
use App\Services\HomepageBlockService;
use Illuminate\Database\Eloquent\Model;
use App\Services\PriceCalculatorService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = [
        'image_small_url_1', 'image_small_url_2', 'image_small_url_3',
        'image_large_url_1', 'image_large_url_2', 'image_large_url_3',
        'image_small_url_1_raw', 'image_small_url_2_raw', 'image_small_url_3_raw',
        'image_large_url_1_raw', 'image_large_url_2_raw', 'image_large_url_3_raw',
        'brand_name', 'category_name'
    ];

    public $timestamps = true;

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function files()
    {
        return $this->hasMany(ProductFile::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')->orderBy('name');
    }

    public function productAttributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function attributeGroupRelation()
    {
        return $this->hasOneThrough(
            AttributeGroup::class,
            AttributeValue::class,
            'id',
            'id',
            'id',
            'attribute_group_id'
        );
    }

    public function activeCampaigns()
    {
        return Campaign::activeAndValid()
            ->whereHas('products', function ($q) {
                $q->where('product_id', $this->id);
            })
            ->with('rules')
            ->get();
    }

    public function getAttributeGroupAttribute()
    {
        $productAttributeValue = ProductAttributeValue::where('product_id', $this->id)->first();

        if (!$productAttributeValue) return null;

        $attributeValue = AttributeValue::find($productAttributeValue->attribute_value_id);

        return $attributeValue ? $attributeValue->attribute->attributeGroup : null;
    }

    public function homepageBlocks()
    {
        return $this->belongsToMany(HomepageBlock::class, 'homepage_block_products')
            ->withPivot('sort_order');
    }

    public function getProductNameAttribute()
    {
        return app()->getLocale() === 'tr' ? ($this->name ?? '') : ($this->name_en ?? '');
    }

    public function getFullNameAttribute()
    {
        return "{$this->product_name} {$this->code}";
    }

    public function getDetailUrlAttribute()
    {
        return route('product.detail', $this->slug);
    }

    public function getUpdateUrlAttribute()
    {
        return '';
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return format_date_time($this->updated_at);
    }

    public function getEncodedCodeAttribute()
    {
        return rawurlencode($this->code);
    }

    public function getImageLazyLoadAttribute()
    {
        return image_url(config('images.default.lazy_load'), 'product.small');
    }

    public function getProductNameShortAttribute()
    {
        return str_limit($this->product_name, 30, '..');
    }

    public function getQuantityValueAttribute()
    {
        return $this->box_quantity_must_be_exact ? $this->box_quantity : 1;
    }

    public function getCanAddToCartAttribute()
    {
        return $this->stockDisplay()['can_add_to_cart'];
    }

    public function getBoxQuantityValueAttribute()
    {
        return $this->box_quantity ?? 1;
    }

    public function getBoxQuantityExactAttribute()
    {
        return $this->box_quantity_must_be_exact ? 'true' : 'false';
    }

    public function getUnitName2TitleAttribute()
    {
        return str_title($this->unit_name_2);
    }

    public function getUnitName3TitleAttribute()
    {
        return str_title($this->unit_name_3);
    }

    public function getUnitName4TitleAttribute()
    {
        return str_title($this->unit_name_4);
    }

    public function getBase64ImageAttribute()
    {
        return base64_image($this->getProductImageLarge(1, false));
    }

    public function productPrice(string $priceType = 'list', $applyCampaign = true)
    {
        if ($priceType === 'special') {
            return 0.0;
        }

        $account = app(CurrentAccountService::class)->currentAccount();

        $context = new ProductPriceContext(
            priceType: $priceType,
            quantity: 0,
            discountRate: 0.0,
            accountRate: (float) ($account->increase_and_decrease_rate ?? 0),
            accountRateType: (int) ($account->increase_and_decrease_type ?? 1),
            currency: $this->getProductCurrency($priceType),
            exchangeType: null,
            applyCampaign: (bool) $applyCampaign
        );

        try {
            $price = app(ProductPriceCalculator::class)->calculate($this, $context)->finalPrice;
        } catch (\InvalidArgumentException $e) {
            logException($e, 'Product::productPrice Invalid Price Type');
            return 0.0;
        }

        return $price > 0 ? $price : 0.0;
    }

    public function getProductPriceShowAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'list');
    }

    public function getProductListPriceShowAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'list');
    }

    public function getProductCashPriceShowAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'cash');
    }

    public function getProductCreditPriceShowAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'credit');
    }

    public function getProductTermPriceShowAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'term');
    }

    public function getProductPriceListAttribute()
    {
        return app(PriceCalculatorService::class)->calculateProductPrice($this, 'list', true);
    }

    public function getProductPriceSpecialAttribute()
    {
        return app(PriceCalculatorService::class)->calculateProductPrice($this, 'special', true);
    }

    public function getProductPriceCashAttribute()
    {
        return app(PriceCalculatorService::class)->calculateProductPrice($this, 'cash', true);
    }

    public function getProductPriceCreditAttribute()
    {
        return app(PriceCalculatorService::class)->calculateProductPrice($this, 'credit', true);
    }

    public function getProductPriceTermAttribute()
    {
        return app(PriceCalculatorService::class)->calculateProductPrice($this, 'term', true);
    }

    public function getProductPriceListFormattedAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'list');
    }

    public function getProductPriceSpecialFormattedAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'special');
    }

    public function getProductPriceCashFormattedAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'cash');
    }

    public function getProductPriceCreditFormattedAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'credit');
    }

    public function getProductPriceTermFormattedAttribute()
    {
        return app(PriceCalculatorService::class)->getProductPriceDisplay($this, 'term');
    }

    public function getProductDiscountRateCashFormattedAttribute()
    {
        $rate = app(PriceCalculatorService::class)->productDiscountRate($this, 'cash');
        return $rate != 0.00 ? '<span>(%' . $rate . ' indirimli)</span>' : '';
    }

    public function getProductDiscountRateCreditFormattedAttribute()
    {
        $rate = app(PriceCalculatorService::class)->productDiscountRate($this, 'credit');
        return $rate != 0.00 ? '<span>(%' . $rate . ' indirimli)</span>' : '';
    }

    public function getProductDiscountRateTermFormattedAttribute()
    {
        $rate = app(PriceCalculatorService::class)->productDiscountRate($this, 'term');
        return $rate != 0.00 ? '<span>(%' . $rate . ' indirimli)</span>' : '';
    }

    public function getProductDiscountRateCashShortAttribute()
    {
        $rate = app(PriceCalculatorService::class)->productDiscountRate($this, 'cash');
        return $rate != 0.00 ? '<span>(%' . $rate . ')</span>' : '';
    }

    public function getProductDiscountRateCreditShortAttribute()
    {
        $rate = app(PriceCalculatorService::class)->productDiscountRate($this, 'credit');
        return $rate != 0.00 ? '<span>(%' . $rate . ')</span>' : '';
    }

    public function getProductDiscountRateTermShortAttribute()
    {
        $rate = app(PriceCalculatorService::class)->productDiscountRate($this, 'term');
        return $rate != 0.00 ? '<span>(%' . $rate . ')</span>' : '';
    }

    public function getProductImageUrl($name, string $size, bool $withVersion)
    {
        if (!$name) return null;

        $url  = image_url($name, "product.{$size}");

        if (!$url) {
            return $this->image_lazy_load;
        }

        return $withVersion ? $url : strtok($url, '?');
    }

    public function getProductImageSmall(int $index = 1, bool $withVersion = true)
    {
        return $this->getProductImageUrl($this->{'image_' . $index}, 'small', $withVersion);
    }

    public function getProductImageLarge(int $index = 1, bool $withVersion = true)
    {
        return $this->getProductImageUrl($this->{'image_' . $index}, 'large', $withVersion);
    }

    public function getImageSmallUrl1Attribute()
    {
        return $this->getProductImageSmall(1);
    }

    public function getImageSmallUrl2Attribute()
    {
        return $this->getProductImageSmall(2);
    }

    public function getImageSmallUrl3Attribute()
    {
        return $this->getProductImageSmall(3);
    }

    public function getImageLargeUrl1Attribute()
    {
        return $this->getProductImageLarge(1);
    }

    public function getImageLargeUrl2Attribute()
    {
        return $this->getProductImageLarge(2);
    }

    public function getImageLargeUrl3Attribute()
    {
        return $this->getProductImageLarge(3);
    }

    public function getImageSmallUrl1RawAttribute()
    {
        return $this->getProductImageSmall(1, false);
    }

    public function getImageSmallUrl2RawAttribute()
    {
        return $this->getProductImageSmall(2, false);
    }

    public function getImageSmallUrl3RawAttribute()
    {
        return $this->getProductImageSmall(3, false);
    }

    public function getImageLargeUrl1RawAttribute()
    {
        return $this->getProductImageLarge(1, false);
    }

    public function getImageLargeUrl2RawAttribute()
    {
        return $this->getProductImageLarge(2, false);
    }

    public function getImageLargeUrl3RawAttribute()
    {
        return $this->getProductImageLarge(3, false);
    }

    public function getProductPriceCurrencyAttribute()
    {
        return $this->getPriceByType('price_');
    }

    public function getProductCurrency($priceType = 'list')
    {
        switch ($priceType) {
            case 'special':
                return '';
                break;
            case 'list':
                return $this->price_1_currency;
                break;
            case 'cash':
                return $this->price_2_currency;
                break;
            case 'credit':
                return $this->price_3_currency;
                break;
            case 'term':
                return $this->price_4_currency;
                break;
        }
    }

    public function getBrandNameAttribute()
    {
        return $this->brand ? ($this->brand->name ?? '') : '';
    }

    public function getBrandImageUrlAttribute()
    {
        return $this->brand->image_url;
    }

    public function getBrandSlugAttribute()
    {
        return $this->brand_id ? $this->brand->url : 'javascript:;';
    }

    public function getCategoryNameAttribute()
    {
        return $this->category ? ($this->category->name ?? '') : '';
    }

    public function getCategorySlugAttribute()
    {
        return $this->category_id ? $this->category->url : 'javascript:;';
    }

    protected static function booted()
    {
        static::saved(fn() => app(HomepageBlockService::class)->clearCache());
        static::deleted(fn() => app(HomepageBlockService::class)->clearCache());
    }

    public function getCasePackageAttribute()
    {
        $case = '';
        $package = '';
        $ball = '';

        $unitNames = [
            $this->unit_name_2,
            $this->unit_name_3,
            $this->unit_name_4,
        ];

        $unitQuantities = [
            $this->unit_quantity_2,
            $this->unit_quantity_3,
            $this->unit_quantity_4,
        ];

        foreach ($unitNames as $index => $name) {
            if (strtoupper($name) === 'KOLİ') {
                $case = $unitQuantities[$index] ?? '1';
            }
            if (strtoupper($name) === 'PAKET') {
                $package = $unitQuantities[$index] ?? '1';
            }
            if (strtoupper($name) === 'TOP') {
                $ball = $unitQuantities[$index] ?? '';
            }
        }

        if ($ball !== '') {
            return $ball;
        }

        // if ($case === '' && $package === '') {
        //     return '';
        // }

        return max($case, 1) . '/' . max($package, 1);
    }
    // Stock
    public function getStockStatusAttribute()
    {
        $display = $this->stockDisplay();
        $stockValue = $display['stock_value'];

        return match ($display['status']) {
            'out_of_stock' => "<span class='badge bg-secondary'>{$stockValue}</span> <span class='badge bg-danger'>1–2 İŞ GÜNÜ</span>",
            'critical_stock' => "<span class='badge bg-secondary'>{$stockValue}</span> <span class='badge bg-warning text-dark'>KRİTİK STOK</span>",
            default => "<span class='badge bg-secondary'>{$stockValue}</span> <span class='badge bg-success'>STOKTA VAR</span>",
        };
    }

    public function getProductStockAttribute()
    {
        return $this->stockDisplay()['product_stock'];
    }

    public function getStockShowAttribute(): ?string
    {
        $stock = $this->stockDisplay()['stock_value'];

        if ($stock === null) {
            return null;
        }

        return trans('translations.product.stok') . ': ' . $stock;
    }

    public function getStockValueAttribute(): string|int|null
    {
        return $this->stockDisplay()['stock_value'];
    }

    public function getStockHtmlAttribute()
    {
        $value = $this->stockDisplay()['stock_value'];

        if ($value === null || $value === '') return '';

        return '<strong>' . trans('translations.product.stok') . ':</strong> ' . e($value);
    }

    private function stockDisplay(): array
    {
        return app(ProductStockDisplayService::class)->displayData($this);
    }
}

