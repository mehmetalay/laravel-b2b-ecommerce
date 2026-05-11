<?php

namespace App\Services;

use App\Application\Cart\DTO\CartContext;
use App\Application\Cart\Services\CartCalculator;
use App\Application\Cart\Factories\CartContextFactory;
use App\Application\Category\Services\CategoryService;
use App\Domain\Product\Factories\ProductStockContextFactory;
use App\Domain\Product\ProductStockPolicy;
use App\Models\Cart;
use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Repositories\CartRepository;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    public function __construct(
        protected CartRepository $repository,
        protected CartCalculator $cartCalculator,
        protected CartContextFactory $cartContextFactory,
        protected ProductStockPolicy $productStockPolicy,
        protected ProductStockContextFactory $productStockContextFactory
    ) {}

    public function create(Request $request)
    {
        return $this->repository->create($request->all());
    }

    public function createRaw(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(Request $request, $cart)
    {
        return $this->repository->update($cart, $request->all());
    }

    public function updateRaw($cart, array $data)
    {
        return $this->repository->update($cart, $data);
    }

    public function delete($cart)
    {
        $this->repository->delete($cart);

        return true;
    }

    public function getFirst($id)
    {
        return $this->repository->getFirst($id);
    }

    public function findCart(array $filters)
    {
        return $this->repository->findCart($filters);
    }

    public function getUserCart(array $filters)
    {
        return $this->repository->getUserCart($filters);
    }

    public function clearUserCart(array $filters)
    {
        return $this->repository->clearUserCart($filters);
    }

    public function restoreBackup($backedUpCartId)
    {
        return $this->repository->restoreBackup($backedUpCartId);
    }

    public function markAsOrdered(array $filters, $currency)
    {
        return $this->repository->markAsOrdered($filters, $currency);
    }

    private static $carts = null;

    public function forgetCache(): void
    {
        self::$carts = null;
    }

    private function resolveContext(?CartContext $context = null): CartContext
    {
        return $context ?? $this->cartContextFactory->fromSession();
    }

    public function carts(?CartContext $context = null)
    {
        $context = $this->resolveContext($context);

        if (self::$carts) {
            return self::$carts;
        }

        $userQuery = app(CurrentAccountService::class)->userQuery();

        $carts = Cart::where('plasiyer_id', ($userQuery['plasiyer_id'] ?? null))
            ->where('user_id', $userQuery['user_id'])
            ->where('sub_dealer_id', ($userQuery['sub_dealer_id'] ?? null))
            ->where('payment_type', $context->paymentType)
            ->orderByRaw("FIELD(currency, 'TL', 'USD', 'EUR', 'GBP')")
            ->active()
            ->orderBy('id', 'DESC')
            ->get();

        return self::$carts = $carts;
    }

    public function cartsByCurrency(string $currency, ?CartContext $context = null)
    {
        return $this->carts($context)->where('currency', $currency);
    }

    public function totalProductPriceBeforeDiscount($currency, ?CartContext $context = null)
    {
        return $this
            ->cartsByCurrency($currency, $context)
            ->where('is_campaign_gift', 0)
            ->reduce(function ($total, $item) {
                return $total + ($item->productPrice() * $item->quantity);
            }, 0);
    }

    public function totalProductPrices($currency, ?CartContext $context = null)
    {
        return $this->cartsByCurrency($currency, $context)->reduce(function ($total, $item) {
            $productPrice = $item->productPrice();
            $discount = $item->effective_discount;

            if ($discount != 0) {
                $productPrice *= (1 - $discount / 100);
            }

            return $total + ($productPrice * $item->quantity);
        }, 0);
    }

    public function totalLineDiscount($currency, ?CartContext $context = null)
    {
        return $this->cartsByCurrency($currency, $context)->reduce(function ($total, $item) {

            $productPrice = $item->productPrice();
            $discountRate = $item->effective_discount;

            if ($discountRate != 0) {
                $discountAmount = $productPrice * ($discountRate / 100);
                return $total + ($discountAmount * $item->quantity);
            }

            return $total;
        }, 0);
    }

    public function cartDiscount1($currency, ?CartContext $context = null)
    {
        $context = $this->resolveContext($context);
        $discountRate = $context->discountRate($currency, 1);

        return $this->totalProductPrices($currency, $context) * ($discountRate / 100);
    }

    public function totalQuantity(?CartContext $context = null)
    {
        $carts = $this->carts($context);
        $currencies = ['TL', 'USD', 'EUR', 'GBP'];

        $result = [];

        foreach ($currencies as $currency) {
            $result[strtolower($currency)] = $carts
                ->where('currency', $currency)
                ->sum('quantity');
        }

        $result['total'] = array_sum($result);

        return $result;
    }

    public function productCount(?CartContext $context = null)
    {
        $carts = $this->carts($context);
        $currencies = ['TL', 'USD', 'EUR', 'GBP'];

        $result = [];

        foreach ($currencies as $currency) {
            $result[strtolower($currency)] = $carts
                ->where('currency', $currency)
                ->unique('product_id')
                ->count();
        }

        $result['count'] = array_sum($result);

        return $result;
    }

    public function totalVat($currency, ?CartContext $context = null)
    {
        $context = $this->resolveContext($context);

        $lines = $this->cartsByCurrency($currency, $context)
            ->where('is_campaign_gift', 0)
            ->map(function ($item) {
                $price = $item->productPrice();
                $discount = $item->effective_discount;
                if ($discount != 0) {
                    $price *= (1 - $discount / 100);
                }

                $lineMatrah = $price * $item->quantity;

                return [
                    'matrah' => $lineMatrah,
                    'vat_rate' => (float) ($item->product->vat_rate ?? 0),
                ];
            });

        $matrahAfterLineDiscount = $lines->sum('matrah');

        if ($matrahAfterLineDiscount <= 0) {
            return 0;
        }

        $extraDiscount =
            ($this->applyCampaignToCartTotals($currency, $context) ?? 0)
            + ($this->cartDiscount1($currency, $context) ?? 0);

        $netMatrah = max(0, $matrahAfterLineDiscount - $extraDiscount);

        $vatTotal = 0;

        foreach ($lines as $line) {
            $rate = (float) $line['vat_rate'];
            $matrah = (float) $line['matrah'];

            if ($matrah <= 0 || $rate <= 0) {
                continue;
            }

            $ratio = $matrah / $matrahAfterLineDiscount;
            $netLineMatrah = $netMatrah * $ratio;

            $vatTotal += $netLineMatrah * ($rate / 100);
        }

        return $vatTotal;
    }

    public function grandTotalWithVat(?CartContext $context = null)
    {
        $context = $this->resolveContext($context);
        $campaignDiscountTotals = [];

        foreach ($context->currencies as $currency) {
            $campaignDiscountTotals[strtolower($currency)] = $this->applyCampaignToCartTotals($currency, $context);
        }

        $calculatorContext = new CartContext(
            currencies: $context->currencies,
            discountRates: $context->discountRates,
            campaignDiscountTotals: $campaignDiscountTotals,
            paymentType: $context->paymentType,
            accountRate: $context->accountRate,
            accountRateType: $context->accountRateType,
            paymentTypeText: $context->paymentTypeText,
            paymentTypeColor: $context->paymentTypeColor,
            freeShippingActive: $context->freeShippingActive,
            manualGiftRemovedCampaignIds: $context->manualGiftRemovedCampaignIds,
            campaignOptOuts: $context->campaignOptOuts
        );

        return $this->cartCalculator->calculate($this->carts($context), $calculatorContext)->toArray();
    }
    public function inspectCartAvailability(?CartContext $context = null): array
    {
        $context = $this->resolveContext($context);
        $categoryService = app(CategoryService::class);
        $paymentType = $context->paymentType;
        $inspections = [];

        foreach ($this->carts($context) as $cart) {
            $product = $cart->product ?? null;

            // 1. Ürün silinmiş / bulunamıyor
            if (!$product) {
                $inspections[] = [
                    'cart' => $cart,
                    'operation' => 'delete',
                    'payload' => [],
                    'warning' => [
                        'product_id' => $cart->product_id,
                        'product_name' => '-',
                        'product_code' => '-',
                        'action' => 'removed',
                        'message' => "#{$cart->product_id} ürünü artık mevcut değil, sepetinizden kaldırıldı.",
                    ],
                ];
                continue;
            }

            // 2. Ürün pasif (status = 0)
            if ($product->status == 0) {
                $inspections[] = [
                    'cart' => $cart,
                    'operation' => 'delete',
                    'payload' => [],
                    'warning' => [
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'product_code' => $product->code,
                        'action' => 'removed',
                        'message' => "\"{$product->product_name}\" ürünü pasif duruma alınmıştır, sepetinizden kaldırıldı.",
                    ],
                ];
                continue;
            }

            // 3. Kategori aktif değil
            $category = $product->category ?? null;
            if (!$categoryService->isCategoryActive($category)) {
                $inspections[] = [
                    'cart' => $cart,
                    'operation' => 'delete',
                    'payload' => [],
                    'warning' => [
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'product_code' => $product->code,
                        'action' => 'removed',
                        'message' => "\"{$product->product_name}\" ürünü kategorisi pasif durumda olduğu için sepetinizden kaldırıldı.",
                    ],
                ];
                continue;
            }

            // 4. Fiyat sıfır / null kontrolü (payment type'a göre)
            if ($paymentType) {
                $currentPrice = $product->productPrice($paymentType, false);
                if ($currentPrice <= 0) {
                    $inspections[] = [
                        'cart' => $cart,
                        'operation' => 'delete',
                        'payload' => [],
                        'warning' => [
                            'product_id' => $product->id,
                            'product_name' => $product->product_name,
                            'product_code' => $product->code,
                            'action' => 'removed',
                            'message' => "\"{$product->product_name}\" ürünü için seçilen ödeme tipinde fiyat bulunamadı, sepetinizden kaldırıldı.",
                        ],
                    ];
                    continue;
                }
            }

            // 4.1 Marka bazlı ödeme tipi kontrolü
            if ($paymentType) {
                $brand = $product->brand;
                if ($brand && !$brand->isPaymentMethodAllowed($paymentType)) {
                    $inspections[] = [
                        'cart' => $cart,
                        'operation' => 'delete',
                        'payload' => [],
                        'warning' => [
                            'product_id' => $product->id,
                            'product_name' => $product->product_name,
                            'product_code' => $product->code,
                            'action' => 'removed',
                            'message' => "\"{$product->product_name}\" ürünü markası ({$brand->name}) için seçilen ödeme tipi izinli değil, sepetinizden kaldırıldı.",
                        ],
                    ];
                    continue;
                }
            }

            // 5. Discount rate senkronizasyonu
            if ($paymentType && !$cart->is_manual_override) {
                $currentDiscount = match ($paymentType) {
                    'cash' => $product->price_2_discount_rate,
                    'credit' => $product->price_3_discount_rate,
                    'term' => $product->price_4_discount_rate,
                    default => 0,
                };

                $currentDiscount = (float) ($currentDiscount ?? 0);
                $cartDiscount = (float) ($cart->discount ?? 0);

                if (abs($currentDiscount - $cartDiscount) > 0.001) {
                    $inspections[] = [
                        'cart' => $cart,
                        'operation' => 'update',
                        'payload' => ['discount' => $currentDiscount],
                        'warning' => [
                            'product_id' => $product->id,
                            'product_name' => $product->product_name,
                            'product_code' => $product->code,
                            'action' => 'discount_updated',
                            'message' => "\"{$product->product_name}\" ürününün indirim oranı %{$cartDiscount} → %{$currentDiscount} olarak güncellendi.",
                        ],
                    ];
                }
            }

            // 6. Paket adedi kontrolü (box_quantity_must_be_exact)
            $boxQuantity = (int) $product->box_quantity;
            if ($product->box_quantity_must_be_exact == 1 && $boxQuantity > 0) {
                if ($cart->quantity % $boxQuantity !== 0) {
                    $oldQuantity = $cart->quantity;
                    $newQuantity = intdiv($oldQuantity, $boxQuantity) * $boxQuantity;

                    if ($newQuantity <= 0) {
                        $inspections[] = [
                            'cart' => $cart,
                            'operation' => 'delete',
                            'payload' => [],
                            'warning' => [
                                'product_id' => $product->id,
                                'product_name' => $product->product_name,
                                'product_code' => $product->code,
                                'action' => 'removed',
                                'message' => "\"{$product->product_name}\" ürününün adedi paket adedine ({$boxQuantity}) uygun olmadığı için sepetinizden kaldırıldı.",
                            ],
                        ];
                        continue;
                    }

                    $inspections[] = [
                        'cart' => $cart,
                        'operation' => 'update',
                        'payload' => ['quantity' => $newQuantity],
                        'warning' => [
                            'product_id' => $product->id,
                            'product_name' => $product->product_name,
                            'product_code' => $product->code,
                            'action' => 'quantity_updated',
                            'message' => "\"{$product->product_name}\" ürününün adedi paket adedine uygun olarak {$oldQuantity} → {$newQuantity} olarak güncellendi.",
                        ],
                    ];
                }
            }

            // 7. Stok kontrolleri
            $availability = $this->productStockPolicy->checkAvailability(
                $product,
                (int) $cart->quantity,
                $this->productStockContextFactory->forAvailability()
            );

            if (!$availability['allow_over_order']) {
                if ($availability['available_stock'] <= 0) {
                    $inspections[] = [
                        'cart' => $cart,
                        'operation' => 'delete',
                        'payload' => [],
                        'warning' => [
                            'product_id' => $product->id,
                            'product_name' => $product->product_name,
                            'product_code' => $product->code,
                            'action' => 'removed',
                            'message' => "\"{$product->product_name}\" ürünü stokta kalmadığı için sepetinizden kaldırıldı.",
                        ],
                    ];
                } elseif (!$availability['is_available']) {
                    $oldQuantity = (int) $cart->quantity;
                    $newQuantity = (int) $availability['accepted_quantity'];
                    $inspections[] = [
                        'cart' => $cart,
                        'operation' => 'update',
                        'payload' => ['quantity' => $newQuantity],
                        'warning' => [
                            'product_id' => $product->id,
                            'product_name' => $product->product_name,
                            'product_code' => $product->code,
                            'action' => 'quantity_updated',
                            'message' => "\"{$product->product_name}\" ürününün adedi stok durumuna göre {$oldQuantity} → {$newQuantity} olarak güncellendi.",
                        ],
                    ];
                }
            }
        }

        return [
            'inspections' => $inspections,
        ];
    }

    public function applyCartFixes(array $inspections): array
    {
        $cartModified = false;
        $warnings = [];

        foreach ($inspections as $inspection) {
            $warnings[] = $inspection['warning'];

            if ($inspection['operation'] === 'delete') {
                $inspection['cart']->delete();
                $cartModified = true;
                continue;
            }

            if ($inspection['operation'] === 'update') {
                $inspection['cart']->update($inspection['payload']);
                $cartModified = true;
            }
        }

        return [
            'modified' => $cartModified,
            'warnings' => $warnings,
        ];
    }

    public function validateCartAvailability(?CartContext $context = null): array
    {
        $inspection = $this->inspectCartAvailability($context);

        return $this->applyCartFixes($inspection['inspections']);
    }

    // Kampanya İle İlgili Fonksiyonlar
    public function applyCampaignToCartTotals(?string $currency = null, ?CartContext $context = null): float
    {
        $items = $this->carts($context);

        if ($currency) {
            $items = $items->where('currency', $currency);
        }

        return (float) $items->reduce(function ($sum, $item) {
            return $sum + (float) ($item->campaign_discount ?? 0);
        }, 0.0);
    }

    public function hasFreeShipping(?CartContext $context = null) // VAR
    {
        return $this->resolveContext($context)->freeShippingActive;
    }

    public function removeCampaignGifts(int $campaignId, ?CartContext $context = null): void // VAR
    {
        $context = $this->resolveContext($context);
        $userQuery = app(CurrentAccountService::class)->userQuery();
        $paymentType = $context->paymentType;

        Cart::where('plasiyer_id', ($userQuery['plasiyer_id'] ?? null))
            ->where('user_id', $userQuery['user_id'])
            ->where('sub_dealer_id', ($userQuery['sub_dealer_id'] ?? null))
            ->where('payment_type', $paymentType)
            ->active()
            ->where('campaign_id', $campaignId)
            ->where('is_campaign_gift', 1)
            ->delete();
    }

    public function expectedFreeProductTotalGifts(Collection $cartItems, Campaign $campaign): int // VAR
    {
        $rule = $campaign->rules->first();
        $extra = $rule?->extra ?? [];

        $minQty = (int) ($extra['min_quantity'] ?? 0);
        $giftPerStack = (int) ($extra['gift_quantity'] ?? 0);
        $isStackable = (int) ($extra['is_stackable'] ?? 0) === 1;

        if ($minQty <= 0 || $giftPerStack <= 0)
            return 0;

        // $triggerItems = $cartItems
        //     ->where('is_campaign_gift', 0)
        //     ->where('campaign_id', (int) $campaign->id);

        // if ($triggerItems->isEmpty()) return 0;

        $totalQty = app(CampaignService::class)->getCampaignTotalQuantity($cartItems, $campaign);

        if ($totalQty < $minQty)
            return 0;

        if (!$isStackable) {
            return $giftPerStack;
        }

        $mult = intdiv($totalQty, $minQty);

        return $mult * $giftPerStack;
    }

    public function hasIncompleteGiftSelection(Campaign $campaign, ?CartContext $context = null): bool // VAR
    {
        $context = $this->resolveContext($context);

        if ($campaign->sub_type !== 'free_product') {
            return false;
        }

        $expected = $this->expectedFreeProductTotalGifts(
            $this->carts($context),
            $campaign
        );

        if ($expected <= 0) {
            return false;
        }

        $selected = $this->carts($context)
            ->where('is_campaign_gift', 1)
            ->where('campaign_id', $campaign->id)
            ->sum('quantity');

        return $selected !== $expected;
    }

    public function getSingleGiftProductIdIfAny(Campaign $campaign): ?int // VAR
    {
        $rule = $campaign->rules->first();
        $extra = $rule?->extra ?? [];

        $giftIds = $extra['gifts'] ?? [];
        if (!is_array($giftIds))
            $giftIds = [$giftIds];

        $giftIds = array_values(array_unique(array_map('intval', $giftIds)));

        return count($giftIds) === 1 ? (int) $giftIds[0] : null;
    }

    public function needsGiftSelection(Campaign $campaign, ?CartContext $context = null): bool
    {
        $context = $this->resolveContext($context);

        if ($campaign->sub_type !== 'free_product') {
            return false;
        }

        if ($context->isManualGiftRemoved((int) $campaign->id)) {
            return true;
        }

        $expected = $this->expectedFreeProductTotalGifts($this->carts($context), $campaign);

        if ($expected <= 0) {
            return false;
        }

        $selected = $this->carts($context)
            ->where('is_campaign_gift', 1)
            ->where('campaign_id', $campaign->id)
            ->sum('quantity');

        return $selected < $expected;
    }
}

