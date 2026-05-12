<?php

namespace App\Http\Controllers;

use App\Application\Cart\DTO\CartContext;
use App\Application\Cart\Factories\CartContextFactory;
use App\Domain\Product\Factories\ProductStockContextFactory;
use App\Domain\Product\ProductStockPolicy;
use App\Models\{Cart, Product, Campaign};
use App\Services\{CartService, FileZipService, CampaignService, BackedUpCartService, CurrentAccountService, CurrencyResolverService, AutoApplyCampaignService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CurrentAccountService $currentAccountService,
        protected BackedUpCartService $backedUpCartService,
        protected CampaignService $campaignService,
        protected CurrencyResolverService $currencyResolverService,
        protected AutoApplyCampaignService $autoApplyCampaignService,
        protected CartContextFactory $cartContextFactory,
        protected ProductStockPolicy $productStockPolicy,
        protected ProductStockContextFactory $productStockContextFactory
    ) {
        $this->middleware('auth:web,subdealer');
    }

    private function cartContext(): CartContext
    {
        return $this->cartContextFactory->fromSession();
    }

    private function discountSessionKeys(?string $currency = null): array
    {
        return $this->cartContextFactory->discountSessionKeys($currency);
    }

    public function index()
    {
        logSession('Sepet sayfasına erişildi.', null, 'info', 'cart_logs');

        return view('frontend.pages.cart.index');
    }

    public function list()
    {
        $this->cartService->forgetCache();

        return view('frontend.pages.cart.list');
    }

    public function header()
    {
        $this->cartService->forgetCache();

        return view('frontend.pages.cart.header');
    }

    public function summary()
    {
        $this->cartService->forgetCache();

        return view('frontend.pages.cart.summary');
    }

    public function headerCount()
    {
        $this->cartService->forgetCache();

        return $this->cartService->productCount($this->cartContext())['count'];
    }

    public function setPaymentType()
    {
        $paymentType = request()->input('payment_type');
        $paymentTypeText = 'Belirtilmemiş';
        $paymentTypeColor = 'text-secondary';

        $currentAccountService = $this->currentAccountService;

        $currentAccount = $currentAccountService->currentAccount();

        if (auth('web')->check() && auth('web')->user()->role === 'salesman' && $currentAccount == null) {
            return response()->json([
                'status' => 'warning',
                'message' => trans('translations.cart_controller.lutfen_bayi_seciniz')
            ]);
        }

        $allowedMethods = explode(',', $currentAccount->allowed_payment_methods);

        $mapping = [
            'cash' => 'cash',
            'credit' => 'credit',
            'term' => 'term',
        ];

        if (!isset($mapping[$paymentType]) || !in_array($mapping[$paymentType], $allowedMethods)) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Bu hesap için seçilen ödeme yöntemi kullanılamıyor.'
            ]);
        }

        if (!in_array($paymentType, ['cash', 'credit', 'term'])) {
            return response()->json(['status' => 'error', 'message' => 'Ödeme türü geçerli değil: ' . $paymentType]);
        }

        switch ($paymentType) {
            case 'cash':
                $paymentTypeText = 'Nakit';
                $paymentTypeColor = 'text-success';
                break;
            case 'credit':
                $paymentTypeText = 'Kredi Kartı';
                $paymentTypeColor = 'text-warning';
                break;
            case 'term':
                $paymentTypeText = 'Vadeli';
                $paymentTypeColor = 'text-danger';
                break;
        }

        $carts = $this->cartService->carts($this->cartContext());

        DB::beginTransaction();

        try {
            foreach ($carts as $cart) {
                $unitPrice = $cart->product->productPrice($paymentType, false);

                if ($unitPrice > 0) {
                    $productDiscount = match ($paymentType) {
                        'cash' => $cart->product->price_2_discount_rate,
                        'credit' => $cart->product->price_3_discount_rate,
                        'term' => $cart->product->price_4_discount_rate,
                        default => 0,
                    };

                    $cart->payment_type = $paymentType;
                    $cart->discount = $productDiscount;
                    $cart->save();
                }
            }

            session(['cart_payment_type' => $paymentType]);
            session(['cart_payment_type_text' => $paymentTypeText]);
            session(['cart_payment_type_color' => $paymentTypeColor]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Ödeme türü güncellendi: ' . $paymentTypeText,
                'payment_type_text' => $paymentTypeText
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Ödeme türü guncellenirken bir hata oluştukt: ' . $e->getMessage()
            ]);
        }
    }

    public function addToCart()
    {
        try {
            $currentAccountService = $this->currentAccountService;
            $cartService = $this->cartService;

            $userQuery = $currentAccountService->userQuery();
            $currentAccount = $currentAccountService->currentAccount();

            if (auth('web')->check() && auth('web')->user()->role === 'salesman' && $currentAccount == null) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.cart_controller.lutfen_bayi_seciniz')
                ]);
            }

            $quantity = request('quantity');
            if ($quantity < 1) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.cart_controller.lutfen_1_ve_uzeri_adet_giriniz')
                ]);
            }

            if ($quantity < additional_setting('purchase_limit_minimum')) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.cart_controller.bu_urunden_en_az_purchaselimitminimum_adet_sepetinize_ekleyiniz', ['purchase_limit_minimum' => additional_setting('purchase_limit_minimum')])
                ]);
            }

            $productId = request('product_id');

            $product = Product::find($productId);

            if (!$product || $product->status == 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => trans('translations.cart_controller.sepete_eklerken_bir_hata_olustu_lutfen_tekrar_deneyiniz')
                ]);
            }

            $paymentType = $this->cartContext()->paymentType;
            if (!$paymentType) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.cart_controller.lutfen_odeme_tipi_seciniz'),
                    'payment_type_selection' => true
                ]);
            }

            $allowedMethods = explode(',', $currentAccount->allowed_payment_methods);
            if (!in_array($paymentType, $allowedMethods)) {
                session()->forget([
                    'cart_payment_type',
                    'cart_payment_type_text',
                    'cart_payment_type_color',
                ]);

                return response()->json([
                    'status' => 'warning',
                    'message' => 'Seçtiğiniz ödeme yöntemi artık geçerli değil. Lütfen yeni bir ödeme türü seçin.'
                ]);
            }

            // Marka bazlı ödeme tipi kontrolü
            $brand = $product->brand;
            if ($brand && !$brand->isPaymentMethodAllowed($paymentType)) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Bu markanın ürünleri için seçilen ödeme türü izinli değil.'
                ]);
            }

            $allowedTypes = ['cash', 'credit', 'term'];
            if (!in_array($paymentType, $allowedTypes)) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.cart_controller.secilen_odeme_tipi_gecersiz')
                ]);
            }

            $unitPrice = $product->productPrice($paymentType, false);
            if ($unitPrice <= 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.cart_controller.secilen_odeme_tipi_icin_urun_fiyati_yok')
                ]);
            }

            $productDiscount = match ($paymentType) {
                'cash' => $product->price_2_discount_rate,
                'credit' => $product->price_3_discount_rate,
                'term' => $product->price_4_discount_rate,
                default => 0,
            };

            $boxQuantity = (int) $product->box_quantity;
            if ($product->box_quantity_must_be_exact == 1 && $boxQuantity > 0) {
                if ($quantity % $boxQuantity !== 0) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => trans('translations.cart_controller.paket_bolunemez_lutfen_boxquantity_adet_seciniz', [
                            'boxquantity' => $boxQuantity
                        ])
                    ]);
                }
            }

            $result = $this->currencyResolverService->resolve(
                $product,
                $paymentType,
                $currentAccountService
            );

            $productCurrency = $result['productCurrency'];
            $exchangeType = $result['exchangeType'];
            $orderSeparately = $result['orderSeparately'];

            $supportedCurrencies = ['TL', 'USD', 'EUR', 'GBP'];
            $accountCurrency = $currentAccount->currency;

            if (!in_array($accountCurrency, $supportedCurrencies) || !in_array($productCurrency, $supportedCurrencies)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Desteklenmeyen bir para birimi kullanılıyor.'
                ]);
            }

            $cart = $cartService->findCart([
                'product_id' => $productId,
                'plasiyer_id' => $userQuery['plasiyer_id'] ?? null,
                'user_id' => $userQuery['user_id'],
                'sub_dealer_id' => $userQuery['sub_dealer_id'] ?? null,
                'payment_type' => $paymentType
            ]);
            if ($cart) {
                $totalQuantity = $cart->quantity + $quantity;
                $availability = $this->productStockPolicy->checkAvailability(
                    $product,
                    (int) $totalQuantity,
                    $this->productStockContextFactory->forAvailability()
                );

                if ($availability['is_available']) {
                    // Satın alma limiti kontrolü
                    if (additional_setting('purchase_limit_maximum') != null && ($totalQuantity > additional_setting('purchase_limit_maximum'))) {
                        $data['status'] = 'warning';
                        $data['message'] = trans('translations.cart_controller.eger_purchaselimitmaximumden_fazla_urun_almak_istiyorsaniz_lutfen_merkezimizle_iletisime_geciniz', ['purchase_limit_maximum' => additional_setting('purchase_limit_maximum')]);
                    } else {
                        $cart->increment('quantity', $quantity);
                        $data['status'] = 'success';
                    }
                } else {
                    $cartStock = max(0, (int) $availability['available_stock'] - (int) $cart->quantity);

                    if ($cartStock < 1) {
                        $data['status'] = 'warning';
                        $data['message'] = trans('translations.cart_controller.bu_urunden_sepetinizde_quantity_adet_bulunmaktadir_yeterli_stok_mevcut_degildir', ['quantity' => $cart->quantity]);
                    } else {
                        $data['status'] = 'warning';
                        $data['message'] = trans('translations.cart_controller.bu_urunden_sepetinizde_quantity_adet_bulunmaktadir_en_fazla_cartstock_adet_daha_ekleyebilirsiniz', ['quantity' => $cart->quantity, 'cart_stock' => $cartStock]);
                        $data['stock'] = $cartStock;
                    }
                }
            } else {
                $availability = $this->productStockPolicy->checkAvailability(
                    $product,
                    (int) $quantity,
                    $this->productStockContextFactory->forAvailability()
                );

                if ($availability['is_available']) {
                    if (additional_setting('purchase_limit_maximum') != null && ($quantity > additional_setting('purchase_limit_maximum'))) {
                        $data['status'] = 'warning';
                        $data['message'] = trans('translations.cart_controller.eger_purchaselimitmaximumden_fazla_urun_almak_istiyorsaniz_lutfen_merkezimizle_iletisime_geciniz', ['purchase_limit_maximum' => additional_setting('purchase_limit_maximum')]);
                    } else {
                        $cartService->createRaw([
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'plasiyer_id' => $userQuery['plasiyer_id'] ?? null,
                            'user_id' => $userQuery['user_id'],
                            'sub_dealer_id' => $userQuery['sub_dealer_id'] ?? null,
                            'currency' => $productCurrency,
                            'exchange_type' => $exchangeType,
                            'order_separately' => $orderSeparately,
                            'payment_type' => $paymentType,
                            'discount' => $productDiscount
                        ]);

                        $data['status'] = 'success';
                    }
                } else {
                    $data['status'] = 'warning';
                    $data['message'] = trans('translations.cart_controller.bu_urunden_en_fazla_stock_adet_alabilirsiniz', ['stock' => $availability['available_stock']]);
                    $data['stock'] = $availability['available_stock'];
                }
            }
            if (isset($data['status']) && $data['status'] == 'success') {
                $data['cart_count'] = $cartService->totalQuantity($this->cartContext())['total'] + $quantity;
                $data['header_cart_list'] = null;
            }

            return response()->json($data);
        } catch (\Throwable $e) {
            logException($e, 'CartController::addToCart', true);
        }
    }

    public function destroy(Cart $cart)
    {
        if ((int) $cart->is_campaign_gift === 1 && (int) $cart->campaign_id > 0) {
            session()->put('manual_gift_removed.' . (int) $cart->campaign_id, true);
        }

        $this->cartService->delete($cart);

        if (count($this->cartService->carts($this->cartContext())) === 0) {
            session()->forget($this->discountSessionKeys());
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function deleteAll()
    {
        $userQuery = $this->currentAccountService->userQuery();

        session()->forget($this->discountSessionKeys());

        $this->cartService->clearUserCart($userQuery);

        $this->cartService->forgetCache();

        if ($this->cartService->carts($this->cartContext())->count() == 0) {
            session()->forget('campaign_opt_outs');
            session()->forget('manual_gift_removed');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sepet başarıyla temizlendi.',
        ]);
    }

    public function updateQuantity(Cart $cart)
    {
        $quantity = (int) request('quantity');
        $product = $cart->product;

        $boxQuantity = (int) $product->box_quantity;
        if ($product->box_quantity_must_be_exact == 1 && $boxQuantity > 0) {
            if ($quantity % $boxQuantity !== 0) {
                $warningMessage = trans('translations.cart_controller.paket_bolunemez_lutfen_boxquantity_adet_seciniz', [
                    'boxquantity' => $boxQuantity
                ]);

                return response()->json([
                    'status' => 'warning',
                    'message' => $warningMessage,
                ]);
            }
        }

        if ($quantity >= additional_setting('purchase_limit_minimum', 1)) {
            $availability = $this->productStockPolicy->checkAvailability(
                $product,
                $quantity,
                $this->productStockContextFactory->forAvailability()
            );
            $productStock = (int) $availability['available_stock'];

            if (additional_setting('purchase_limit_maximum') != null && ($quantity > additional_setting('purchase_limit_maximum'))) {
                $data['warning'] = trans('translations.cart_controller.eger_purchaselimitmaximumden_fazla_urun_almak_istiyorsaniz_lutfen_merkezimizle_iletisime_geciniz', ['purchase_limit_maximum' => additional_setting('purchase_limit_maximum')]);
            } else {
                if ($availability['is_available']) {
                    $updateQuantity = $quantity;
                    $data['success'] = trans('translations.cart_controller.basariyla_adet_guncellendi');
                } else {
                    $updateQuantity = (int) $availability['accepted_quantity'];
                    $data['warning'] = trans('translations.cart_controller.stoklarimizda_bu_urunden_productstock_adet_bulunmaktadir_girdiginiz_miktar_mevcut_stoktan_daha_yuksek_oldugu_icin_stok_sayisi_productstock_adet_olarak_guncellenmistir', ['productStock' => $productStock]);
                    $data['input_quantity'] = $productStock;
                }

                $this->cartService->updateRaw($cart, [
                    'quantity' => $updateQuantity
                ]);
            }
        } else {
            $data['warning'] = trans('translations.cart_controller.lutfen_miktari_purchaselimitminimum_ve_uzeri_giriniz', ['purchase_limit_minimum' => additional_setting('purchase_limit_minimum')]);
        }

        $this->cartService->forgetCache();

        $this->autoApplyCampaignService->sync($this->cartService->carts($this->cartContext()));

        if ($this->cartService->carts($this->cartContext())->count() == 0) {
            session()->forget('campaign_opt_outs');
            session()->forget('manual_gift_removed');
        }

        if (isset($data['success'])) {
            $data['status'] = 'success';
            $data['message'] = $data['message'] ?? $data['success'];
            unset($data['success']);
        } elseif (isset($data['warning'])) {
            $data['status'] = 'warning';
            $data['message'] = $data['message'] ?? $data['warning'];
            unset($data['warning']);
        } elseif (isset($data['error'])) {
            $data['status'] = 'error';
            $data['message'] = $data['message'] ?? $data['error'];
            unset($data['error']);
        }

        $statusCode = ($data['status'] ?? null) === 'error' ? 400 : 200;

        return response()->json($data, $statusCode);
    }

    public function updateExplanation(Request $request, Cart $cart)
    {
        $this->cartService->update($request, $cart);

        $message = trans('translations.cart_controller.basariyla_aciklama_girildi');

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }

    public function updateDiscount(Request $request, Cart $cart)
    {
        $discount = (int) request('discount');

        if (!empty($discount) && !($discount >= 1 && $discount <= 100)) {
            $data['warning'] = trans('translations.cart_controller.lutfen_indirim_oranini_1_ile_100_arasinda_bir_yuzde_olarak_giriniz');
        } else {
            $request->merge(['is_manual_override' => 1]);
            $this->cartService->update($request, $cart);

            $data['success'] = trans('translations.cart_controller.basariyla_urun_fiyatina_indirim_uygulandi');
        }

        if (isset($data['success'])) {
            $data['status'] = 'success';
            $data['message'] = $data['message'] ?? $data['success'];
            unset($data['success']);
        } elseif (isset($data['warning'])) {
            $data['status'] = 'warning';
            $data['message'] = $data['message'] ?? $data['warning'];
            unset($data['warning']);
        }

        $statusCode = ($data['status'] ?? null) === 'error' ? 400 : 200;

        return response()->json($data, $statusCode);
    }

    public function updatePrice(Request $request, Cart $cart)
    {
        if ($cart->campaign_id && $cart->campaign_rule_type === 'tiered_price') {
            $warningMessage = 'Bu ürün kampanyalı fiyata sahiptir. Fiyatını değiştiremezsiniz.';

            return response()->json([
                'status' => 'warning',
                'message' => $warningMessage,
            ]);
        }

        $discount = request('discount');

        if (!empty($discount) && !($discount >= 1 && $discount <= 100)) {
            $data['warning'] = trans('translations.cart_controller.lutfen_indirim_oranini_1_ile_100_arasinda_bir_yuzde_olarak_giriniz');
        } else {
            $request->merge(['is_manual_override' => 1]);
            $this->cartService->update($request, $cart);

            $data['success'] = trans('translations.cart_controller.basariyla_urun_fiyatina_indirim_uygulandi');
        }

        if (isset($data['success'])) {
            $data['status'] = 'success';
            $data['message'] = $data['message'] ?? $data['success'];
            unset($data['success']);
        } elseif (isset($data['warning'])) {
            $data['status'] = 'warning';
            $data['message'] = $data['message'] ?? $data['warning'];
            unset($data['warning']);
        }

        return response()->json($data);
    }

    public function generalDiscount()
    {
        $carts = $this->cartService->carts($this->cartContext());
        $data = [];

        if (!count($carts)) {
            $warningMessage = trans('translations.cart_controller.lutfen_sepette_indirim_uygulamak_icin_en_az_bir_urun_ekleyin');

            return response()->json([
                'status' => 'warning',
                'message' => $warningMessage,
            ]);
        }

        $discountRate = request('discount');
        $currency = request('currency');

        if ($discountRate <= 1 || $discountRate >= 100) {
            $warningMessage = trans('translations.cart_controller.lutfen_indirim_oranini_1_ile_100_arasinda_bir_yuzde_olarak_giriniz');

            return response()->json([
                'status' => 'warning',
                'message' => $warningMessage,
            ]);
        }

        if (in_array($currency, ['tl', 'usd', 'eur', 'gbp'])) {
            $session_key_1 = "cart_discount_rate_{$currency}_1";
            $session_key_2 = "cart_discount_rate_{$currency}_2";

            if (session()->has($session_key_1)) { // && session()->has($session_key_2)
                $data['warning'] = trans('translations.cart_controller.indirim_zaten_uygulanmis');
            } else {
                if (!session()->has($session_key_1)) {
                    session()->put($session_key_1, $discountRate);
                }
                // elseif (!session()->has($session_key_2)) {
                //     session()->put($session_key_2, $discountRate);
                // }

                $data['success'] = trans('translations.cart_controller.indirim_basariyla_uygulandi');
            }
        } else {
            $data['warning'] = trans('translations.cart_controller.gecersiz_doviz_birimi');
        }

        if (isset($data['success'])) {
            $data['status'] = 'success';
            $data['message'] = $data['message'] ?? $data['success'];
            unset($data['success']);
        } elseif (isset($data['warning'])) {
            $data['status'] = 'warning';
            $data['message'] = $data['message'] ?? $data['warning'];
            unset($data['warning']);
        }

        return response()->json($data);
    }

    public function cancelAllDiscounts()
    {
        $currency = request('currency');

        $discountKeys = [
            "cart_discount_rate_{$currency}_1",
            "cart_discount_rate_{$currency}_2"
        ];

        $hasDiscount = false;
        foreach ($discountKeys as $key) {
            if (session()->has($key)) {
                $hasDiscount = true;
                break;
            }
        }

        if ($hasDiscount) {
            session()->forget($discountKeys);
            $data['success'] = trans('translations.cart_controller.sepet_indirimi_iptal_edildi');
        } else {
            $data['warning'] = trans('translations.cart_controller.henuz_indirim_uygulanmamis');
        }

        if (isset($data['success'])) {
            $data['status'] = 'success';
            $data['message'] = $data['message'] ?? $data['success'];
            unset($data['success']);
        } elseif (isset($data['warning'])) {
            $data['status'] = 'warning';
            $data['message'] = $data['message'] ?? $data['warning'];
            unset($data['warning']);
        }

        return response()->json($data);
    }

    public function import()
    {
        if (request('cart_name') == '') {
            $data['warning'] = trans('translations.cart_controller.lutfen_sepet_adini_giriniz');
        } else {
            $backedUpCart = $this->backedUpCartService->create(request());

            foreach ($this->cartService->carts($this->cartContext()) as $cart) {
                $this->cartService->updateRaw($cart, [
                    'backed_up' => 1,
                    'backed_up_cart_id' => $backedUpCart->id
                ]);
            }

            session()->forget($this->discountSessionKeys());

            session()->flash('success', trans('translations.cart_controller.sepeti_basariyla_yedeklendi'));

            $data['status'] = 'success';
            $data['message'] = trans('translations.cart_controller.sepeti_basariyla_yedeklendi');
        }

        if (isset($data['warning'])) {
            $data['status'] = 'warning';
            $data['message'] = $data['message'] ?? $data['warning'];
            unset($data['warning']);
        }

        return response()->json($data);
    }

    public function export()
    {
        $backedUpCartId = request('backed_up_cart_id');

        if ($backedUpCartId) {
            $backedUpCart = $this->backedUpCartService->getFirst($backedUpCartId);

            if ($backedUpCart) {
                $this->currentAccountService->switchDealer($backedUpCart->user, false);

                $this->cartService->restoreBackup($backedUpCartId);

                if ($backedUpCart->cart_discount_rate_tl_1 != 0) {
                    session()->put('cart_discount_rate_tl_1', $backedUpCart->cart_discount_rate_tl_1);
                    session()->put('cart_discount_rate_tl_2', $backedUpCart->cart_discount_rate_tl_2);
                }

                if ($backedUpCart->cart_discount_rate_usd_1 != 0) {
                    session()->put('cart_discount_rate_usd_1', $backedUpCart->cart_discount_rate_usd_1);
                    session()->put('cart_discount_rate_usd_2', $backedUpCart->cart_discount_rate_usd_2);
                }

                if ($backedUpCart->cart_discount_rate_eur_1 != 0) {
                    session()->put('cart_discount_rate_eur_1', $backedUpCart->cart_discount_rate_eur_1);
                    session()->put('cart_discount_rate_eur_2', $backedUpCart->cart_discount_rate_eur_2);
                }

                if ($backedUpCart->cart_discount_rate_gbp_1 != 0) {
                    session()->put('cart_discount_rate_gbp_1', $backedUpCart->cart_discount_rate_gbp_1);
                    session()->put('cart_discount_rate_gbp_2', $backedUpCart->cart_discount_rate_gbp_2);
                }

                $this->backedUpCartService->delete($backedUpCart);

                session()->flash('success', trans('translations.cart_controller.sepeti_basariyla_ice_aktarildi'));

                $data['status'] = 'success';
                $data['message'] = trans('translations.cart_controller.sepeti_basariyla_ice_aktarildi');
            } else {
                $data['status'] = 'error';
                $data['message'] = trans('translations.cart_controller.bir_hata_olustu_lutfen_tekrar_deneyiniz');
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = trans('translations.cart_controller.lutfen_listelerden_sepeti_seciniz');
        }

        if (isset($data['error'])) {
            unset($data['error']);
        }
        if (isset($data['warning'])) {
            $data['status'] = 'warning';
            $data['message'] = $data['message'] ?? $data['warning'];
            unset($data['warning']);
        }

        return response()->json($data);
    }

    public function downloadAllImages()
    {
        $carts = $this->cartService->carts($this->cartContext());

        $zipFilePath = app(FileZipService::class)->createImageZip($carts, 'sepet');

        return response()->download($zipFilePath)->deleteFileAfterSend();
    }

    public function applyCampaign(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|integer|exists:campaigns,id',
        ]);

        $campaign = Campaign::with(['rules', 'products'])
            ->activeAndValid()
            ->findOrFail($request->campaign_id);

        session()->forget('campaign_opt_outs');
        session()->forget('manual_gift_removed.' . (int) $campaign->id);
        session()->forget('cart_free_shipping_active');

        $campaignProductIds = $campaign->products->pluck('id')->toArray();

        $cartItems = $this->cartService->carts($this->cartContext())
            ->whereIn('product_id', $campaignProductIds)
            ->where('is_campaign_gift', 0);

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sepetinizde bu kampanyaya ait ürün yok.'
            ], 422);
        }

        $appliedAny = false;

        foreach ($cartItems as $item) {

            if ($this->campaignService->checkEligibilityForCart($this->cartService->carts($this->cartContext()), $campaign)) {

                if ($item->campaign_id && (int) $item->campaign_id !== (int) $campaign->id) {
                    $this->cartService->removeCampaignGifts((int) $item->campaign_id, $this->cartContext());
                }

                $item->update([
                    'campaign_id' => $campaign->id,
                    'campaign_rule_type' => $campaign->sub_type,
                ]);

                $appliedAny = true;
            }
        }

        if (!$appliedAny) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sepetiniz bu kampanyanın şartlarını sağlamıyor.'
            ], 422);
        }

        $this->autoApplyCampaignService->sync($this->cartService->carts($this->cartContext()));

        if ($campaign->sub_type === 'free_product') {

            $singleGiftId = $this->cartService->getSingleGiftProductIdIfAny($campaign);
            $expected = $this->cartService->expectedFreeProductTotalGifts(
                $this->cartService->carts($this->cartContext()),
                $campaign
            );

            if ($singleGiftId && $expected > 0) {
                return response()->json([
                    'status' => 'success',
                    'requires_gift_selection' => false,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'requires_gift_selection' => true,
                'campaign_id' => $campaign->id,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function campaignModal()
    {
        $cartService = $this->cartService;
        $campaignService = $this->campaignService;

        $allCampaigns = Campaign::with(['rules', 'products'])
            ->activeAndValid()
            ->get();

        return view('frontend.pages.cart.partials._campaign-modal', compact('allCampaigns', 'cartService', 'campaignService'));
    }

    public function campaignModalBody()
    {
        $cartService = $this->cartService;
        $campaignService = $this->campaignService;

        $allCampaigns = Campaign::with(['rules', 'products'])
            ->activeAndValid()
            ->get();

        return view('frontend.pages.cart.partials._campaign-modal-body', compact('allCampaigns', 'cartService', 'campaignService'));
    }

    public function removeCampaign()
    {
        $items = $this->cartService->carts($this->cartContext());

        $ids = $items
            ->pluck('campaign_id')
            ->filter()
            ->unique();

        foreach ($ids as $cid) {
            session()->put(
                'campaign_opt_outs',
                collect(session('campaign_opt_outs', []))
                    ->push((int) $cid)
                    ->unique()
                    ->values()
                    ->all()
            );

            session()->forget('manual_gift_removed.' . (int) $cid);
        }

        foreach ($items->where('is_campaign_gift', 1) as $gift) {
            $gift->delete();
        }

        foreach ($items->where('is_campaign_gift', 0) as $item) {
            if ($item->campaign_id || $item->campaign_rule_type) {
                $item->update(['campaign_id' => null, 'campaign_rule_type' => null]);
            }
        }

        session()->forget('cart_free_shipping_active');

        $this->autoApplyCampaignService->sync($this->cartService->carts($this->cartContext()));

        return response()->json(['status' => 'success']);
    }

    public function removeSingleCampaign(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|integer',
        ]);

        $campaignId = (int) $request->campaign_id;

        session()->put(
            'campaign_opt_outs',
            collect(session('campaign_opt_outs', []))
                ->push((int) $campaignId)
                ->unique()
                ->values()
                ->all()
        );

        session()->forget('manual_gift_removed.' . $campaignId);

        foreach ($this->cartService->carts($this->cartContext())->where('is_campaign_gift', 1) as $gift) {
            if ((int) $gift->campaign_id === $campaignId) {
                $gift->delete();
            }
        }

        foreach ($this->cartService->carts($this->cartContext())->where('is_campaign_gift', 0) as $item) {
            if ((int) $item->campaign_id === $campaignId) {
                $item->update([
                    'campaign_id' => null,
                    'campaign_rule_type' => null,
                ]);
            }
        }

        session()->forget('cart_free_shipping_active');

        $this->autoApplyCampaignService->sync($this->cartService->carts($this->cartContext()));

        return response()->json(['status' => 'success']);
    }

    public function freeProductGiftModal(Request $request)
    {
        $campaign = Campaign::with(['rules', 'products'])
            ->activeAndValid()
            ->findOrFail($request->campaign_id);

        $rule = $campaign->rules->first();
        $extra = $rule->extra ?? [];

        $giftIds = (array) ($extra['gifts'] ?? []);
        $gifts = Product::whereIn('id', $giftIds)->get();

        $giftLimit = $this->cartService->expectedFreeProductTotalGifts($this->cartService->carts($this->cartContext()), $campaign);

        $selectedGifts = $this->cartService->carts($this->cartContext())
            ->where('is_campaign_gift', 1)
            ->where('campaign_id', (int) $campaign->id)
            ->groupBy('product_id')
            ->map(fn($rows) => (int) $rows->sum('quantity'))
            ->toArray();

        return view('frontend.pages.cart.partials._free-product-gift-modal', compact('campaign', 'gifts', 'giftLimit', 'selectedGifts'));
    }

    public function selectFreeProductGifts(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|integer|exists:campaigns,id',
            'gifts' => 'required|array',
        ]);

        $campaign = Campaign::with(['rules', 'products'])
            ->activeAndValid()
            ->findOrFail($request->campaign_id);

        abort_unless($campaign->sub_type === 'free_product', 404);

        $hasAppliedRow = $this->cartService->carts($this->cartContext())
            ->where('is_campaign_gift', 0)
            ->where('campaign_id', $campaign->id)
            ->count() > 0;

        if (!$hasAppliedRow) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bu kampanya sepette uygulanmış değil.'
            ], 422);
        }

        if (!$this->campaignService->isCampaignEligibleForCart($this->cartService->carts($this->cartContext()), $campaign)) {
            $this->cartService->removeCampaignGifts($campaign->id, $this->cartContext());

            return response()->json([
                'status' => 'error',
                'message' => 'Sepet şartları artık sağlamıyor.'
            ], 422);
        }

        $rule = $campaign->rules->first();
        $extra = $rule?->extra ?? [];

        $giftLimit = $this->cartService->expectedFreeProductTotalGifts(
            $this->cartService->carts($this->cartContext()),
            $campaign
        );

        $selected = collect($request->input('gifts', []))
            ->map(fn($qty) => (int) $qty)
            ->filter(fn($qty) => $qty > 0);

        if ($selected->sum() !== $giftLimit) {
            return response()->json([
                'status' => 'error',
                'message' => "Toplam hediye adedi {$giftLimit} olmalı."
            ], 422);
        }

        $allowedGiftIds = $extra['gifts'] ?? [];

        if (!is_array($allowedGiftIds))
            $allowedGiftIds = [$allowedGiftIds];

        foreach ($selected->keys() as $giftId) {
            if (!in_array((int) $giftId, array_map('intval', $allowedGiftIds), true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Geçersiz hediye seçimi.'
                ], 422);
            }
        }

        $this->cartService->removeCampaignGifts($campaign->id, $this->cartContext());

        $currentAccountService = $this->currentAccountService;
        $userQuery = $currentAccountService->userQuery();
        $paymentType = $this->cartContext()->paymentType;

        foreach ($selected as $giftId => $qtySelected) {

            $product = Product::findOrFail((int) $giftId);

            $result = $this->currencyResolverService->resolve(
                $product,
                $paymentType,
                $currentAccountService
            );

            $productCurrency = $result['productCurrency'];
            $exchangeType = $result['exchangeType'];
            $orderSeparately = $result['orderSeparately'];

            $this->cartService->createRaw([
                'product_id' => (int) $giftId,
                'quantity' => (int) $qtySelected,
                'plasiyer_id' => $userQuery['plasiyer_id'] ?? null,
                'user_id' => $userQuery['user_id'],
                'sub_dealer_id' => $userQuery['sub_dealer_id'] ?? null,
                'currency' => $productCurrency,
                'exchange_type' => $exchangeType,
                'order_separately' => $orderSeparately,
                'payment_type' => $paymentType,
                'campaign_id' => $campaign->id,
                'is_campaign_gift' => 1,
                'campaign_rule_type' => 'free_product',
            ]);

            session()->forget('manual_gift_removed.' . (int) $request->campaign_id);
        }

        return response()->json(['status' => 'success']);
    }

    public function addSameProductGift(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|integer|exists:campaigns,id',
            'cart_id' => 'required|integer|exists:carts,id',
        ]);

        $campaign = Campaign::with(['rules'])
            ->activeAndValid()
            ->findOrFail($request->campaign_id);

        abort_unless($campaign->sub_type === 'free_product', 404);

        if (!$this->campaignService->freeProductAllowsSameProduct($campaign)) {
            abort(403);
        }

        $cart = Cart::findOrFail($request->cart_id);

        $this->cartService->removeCampaignGifts($campaign->id, $this->cartContext());

        $giftQty = $this->cartService->expectedFreeProductTotalGifts(
            $this->cartService->carts($this->cartContext()),
            $campaign
        );

        if ($giftQty <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hediye adedi hesaplanamadı.'
            ], 422);
        }

        $this->cartService->createRaw([
            'product_id' => $cart->product_id,
            'quantity' => $giftQty,
            'plasiyer_id' => $cart->plasiyer_id,
            'user_id' => $cart->user_id,
            'sub_dealer_id' => $cart->sub_dealer_id,
            'currency' => $cart->currency,
            'exchange_type' => $cart->exchange_type,
            'order_separately' => $cart->order_separately,
            'payment_type' => $cart->payment_type,
            'campaign_id' => $campaign->id,
            'is_campaign_gift' => 1,
            'campaign_rule_type' => 'free_product',
        ]);

        return response()->json(['status' => 'success']);
    }
}


