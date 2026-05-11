<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class CurrentAccountService
{
    private static $currentAccount = null;

    public function currentAccount()
    {
        if (self::$currentAccount) {
            return self::$currentAccount;
        }

        $user = null;

        if (Auth::guard('web')->check()) {
            $userRole = Auth::guard('web')->user()->role;

            switch ($userRole) {
                case 'salesman': // Plasiyer giriş yaptıysa → acting_dealer_id seçilmiş mi?
                    if ($dealerId = session('acting_dealer_id')) {
                        $user = app(DealerService::class)->getFirst($dealerId);
                    }
                    break;

                case 'dealer': // Bayi giriş yaptıysa → acting_subdealer_id seçilmiş mi?
                    if ($subDealerId = session('acting_subdealer_id')) {
                        $user = app(SubDealerService::class)->getFirst($subDealerId)->dealer;
                    } else {
                        $user = Auth::guard('web')->user();
                    }
                    break;
            }
        }
        elseif (Auth::guard('subdealer')->check()) {
            $user = Auth::guard('subdealer')->user()->dealer;
        }

        return self::$currentAccount = $user;
    }

    public function userQuery($onlyUser = false)
    {
        $currentAccount = $this->currentAccount();
        $query = [];

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            switch ($user->role) {
                case 'salesman': // Plasiyer
                    $query = [
                        'user_id' => $currentAccount ? $currentAccount->current_account_id : null,
                        'creator_type' => 'salesman'
                    ];
                    if (!$onlyUser) {
                        $query['plasiyer_id'] = $user->current_account_id;
                    }
                    $query['order_status'] = 'approved';
                    break;

                case 'dealer': // Bayi
                    if ($subDealerId = session('acting_subdealer_id')) {
                        $query = [
                            'user_id' => $currentAccount->current_account_id,
                            'sub_dealer_id' => app(SubDealerService::class)->getFirst($subDealerId)->id,
                        ];
                    } else {
                        $query = [
                            'user_id' => $user->current_account_id,
                        ];
                    }

                    $query['creator_type'] = 'dealer';
                    $query['order_status'] = 'approved';
                    break;
            }
        }

        elseif (Auth::guard('subdealer')->check()) {
            $subDealer = Auth::guard('subdealer')->user();

            $query = [
                'user_id' => $currentAccount->current_account_id,
                'sub_dealer_id' => app(SubDealerService::class)->getFirst($subDealer->id)->id,
                'creator_type' => 'subdealer',
                'order_status' => $subDealer->can_approve_order ? 'pending' : 'approved'
            ];
        }

        return $query;
    }

    public function priceType()
    {
        $currentAccount = $this->currentAccount();

        if (Auth::guard('web')->check()) {
            $userRole = Auth::guard('web')->user()->role;

            if ($userRole === 'dealer' || ($userRole === 'salesman' && $currentAccount != null)) {
                $priceType = $userRole === 'salesman' ? $currentAccount->price_type : Auth::guard('web')->user()->price_type;
            }

            if ($userRole === 'salesman' && Auth::guard('web')->user()->show_retail_price == 1) {
                $priceType = 1;
            }
        } else if (auth('subdealer')->check()) {
            $priceType = $currentAccount->price_type;
        }

        return $priceType ?? 1;
    }

    public function currentAccountBalance()
    {
        $currentAccount = $this->currentAccount();

        return $currentAccount ? ($currentAccount->currency == 'TL' ? $currentAccount->balance : $currentAccount->currency_balance) : 0;
    }

    public function getUserSummary()
    {
        $currencyService = app(CurrencyService::class);
        $currentAccount  = $this->currentAccount();

        $summary = '';

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            switch ($user->role) {
                case 'salesman':
                    if ($currentAccount) {
                        $balance = $this->currentAccountBalance();
                        $formatPrice = $currencyService->formatPrice($balance, $currentAccount->currency);
                        $currentAccountName = str_limit($currentAccount->name, 25);

                        $summary .= "<span class='text-white fs-6'>{$currentAccountName}</span> {$formatPrice}";

                        if ($switchId = $this->findTargetUserForCurrencySwitch()) {
                            $route = route('switch.account', [$switchId]);
                            $summary .= " <a href='{$route}' class='text-white' onclick='showLoader()'>| <i class='fa-solid fa-sync text-info'></i></a>";
                        }
                    } else {
                        $summary .= trans('translations.header.bayi_secilmedi');
                    }
                    break;

                case 'dealer':
                    $balance = $this->currentAccountBalance();
                    $formatPrice = $currencyService->formatPrice($balance, $currentAccount->currency);
                    $currentAccountName = str_limit($currentAccount->name, 25);

                    $summary .= "<span class='text-white fs-6'>{$currentAccountName}</span> {$formatPrice}";

                    if ($subDealerId = session('acting_subdealer_id')) {
                        $subDealerName = str_limit(app(SubDealerService::class)->getFirst($subDealerId)->name, 25);
                        $summary .= "<div>Alt Bayi: {$subDealerName} | <a href='javascript:;' cancel-sub-dealer><span class='badge alert-danger'>İptal Et</span></a></div>";
                    } else {
                        if ($currentAccount->subDealers->count()) {
                            $summary .= "<div>" . trans('translations.header.bayi_secilmedi') . "</div>";
                        }
                    }
                    break;
            }
        }
        elseif (Auth::guard('subdealer')->check()) {
            $currentAccountName = str_limit($currentAccount->name, 25);

            $summary .= "<span class='text-white fs-6'>{$currentAccountName}</span>";
        }

        return $summary;
    }

    public function groupCurrencyStatus()
    {
        $currentUser = $this->currentAccount();

        if (!$currentUser) {
            return [
                'has_tl' => false,
                'has_usd' => false,
                'has_eur' => false,
                'has_gbp' => false
            ];
        }

        $otherUsers = $this->getGroupUsers($currentUser);

        if ($otherUsers->count() === 1) {
            $otherUsers = $otherUsers->pluck('currency')->unique();
        } else {
            $otherUsers = collect();
        }

        $userCurrency = $currentUser->currency;

        $hasTL = $userCurrency === 'TL' || $otherUsers->contains('TL');
        $hasUSD = $userCurrency === 'USD' || $otherUsers->contains('USD');
        $hasEUR = $userCurrency === 'EUR' || $otherUsers->contains('EUR');
        $hasGBP = $userCurrency === 'GBP' || $otherUsers->contains('GBP');

        return [
            'has_tl' => $hasTL,
            'has_usd' => $hasUSD,
            'has_eur' => $hasEUR,
            'has_gbp' => $hasGBP
        ];
    }

    public function findTargetUserForCurrencySwitch()
    {
        $currentUser = $this->currentAccount();

        if (!$currentUser) {
            return null;
        }

        $otherUsers = $this->getGroupUsers($currentUser);

        if ($otherUsers->count() === 1) {
            return $otherUsers->first()->id;
        }

        return null;
    }

    public function getGroupUsers($currentUser)
    {
        $groupCode = $currentUser->group_code;
        $currentAccountId = $currentUser->current_account_id;
        $currency = $currentUser->currency;

        return Cache::remember("group_users_{$groupCode}", now()->addDay(), function () use ($currentAccountId, $currency, $groupCode) {
            return User::whereNotNull('group_code')
                ->where('current_account_id', '!=', $currentAccountId)
                ->where('currency', ($currency === 'TL' ? 'USD' : 'TL'))
                ->where('group_code', $groupCode)
                ->active()
                ->get();
        });
    }

    public function switchDealer($user, $applyDiscounts = true)
    {
        session()->put('acting_dealer_id', $user->id);

        $this->clearCartDiscounts();

        if ($applyDiscounts) {
            $this->applyDealerDiscounts($user);
        }
    }

    private function clearCartDiscounts()
    {
        $discountKeys = [
            'cart_discount_rate_tl_1', 'cart_discount_rate_tl_2',
            'cart_discount_rate_usd_1', 'cart_discount_rate_usd_2',
            'cart_discount_rate_eur_1', 'cart_discount_rate_eur_2',
            'cart_discount_rate_gbp_1', 'cart_discount_rate_gbp_2'
        ];

        session()->forget($discountKeys);
    }

    private function applyDealerDiscounts($user)
    {
        $authCode = Auth::guard('web')->check() ? Auth::guard('web')->user()->code : 0;
        $currencyKey = 'cart_discount_rate_' . Str::lower($user->currency) . '_';

        if ($user->plasiyer1 === $authCode) {
            $this->setDiscountSession($currencyKey, $user->plasiyer1_discount1, $user->plasiyer1_discount2);
        } elseif ($user->plasiyer2 === $authCode) {
            $this->setDiscountSession($currencyKey, $user->plasiyer2_discount1, $user->plasiyer2_discount2);
        }
    }

    private function setDiscountSession($key, $discount1, $discount2)
    {
        if ($discount1 != 0) {
            session()->put($key . '1', $discount1);
        }

        if ($discount2 != 0) {
            session()->put($key . '2', $discount2);
        }
    }

}
