<?php

namespace App\Http\Controllers;

use App\Models\{User, SubDealer};
use Illuminate\Support\Str;

class CurrentAccountController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:web,subdealer', ['except' => ['search']]);
    }

    public function index()
    {
        $search = request()->get('search');
        $q = "%{$search}%";

        if (auth('web')->check() && auth('web')->user()->role === 'salesman') {
            $accessType = auth('web')->user()->access_type;
            $salesmanCode = auth('web')->user()->code;

            $items = User::customer()
                ->when($accessType === 'specific_code', function ($query) use ($salesmanCode) {
                    $query->where(function ($query) use ($salesmanCode) {
                        $query->where('plasiyer1', $salesmanCode)
                            ->orWhere('plasiyer2', $salesmanCode)
                            ->orWhere('plasiyer3', $salesmanCode)
                            ->orWhere('plasiyer4', $salesmanCode)
                            ->orWhere('plasiyer5', $salesmanCode);
                        });
                })
                ->when($search, function ($query) use ($q) {
                    $query->where(function ($query) use($q) {
                        $query->where('name', 'like', $q)
                            ->orWhere('code', 'like', $q)
                            ->orWhere('province', 'like', $q)
                            ->orWhere('district', 'like', $q)
                            ->orWhere('address', 'like', $q);
                    });
                })
                ->where('status', 1)
                ->paginate(50);
        } else if (auth('web')->check() && auth('web')->user()->role === 'dealer') {
            $items = SubDealer::query()
                ->where('dealer_id', auth('web')->user()->current_account_id)
                ->when($search, function ($query) use ($q) {
                    $query->where('name', 'like', $q);
                })
                ->where('status', 1)
                ->paginate(50);
        }

        return response()->json([
            'status' => 'success',
            'html'   => view('current-accounts.index',  compact('items'))->render(),
        ]);
    }

    public function select($id)
    {
        if (auth('web')->check() && auth('web')->user()->role === 'salesman') {
            $user = User::find($id);
        } else if (auth('web')->check() && auth('web')->user()->role === 'dealer') {
            $user = SubDealer::find($id);
        }

        if (!isset($user)) {
            return response()->json([
                'status' => 'error',
                'message' => trans('translations.current_account_controller.lutfen_tekrar_deneyiniz')
            ], 404);
        }

        $this->switchUserAndApplyDiscounts($user);

        session()->forget([
            'cart_payment_type_text',
            'cart_payment_type',
            'cart_payment_type_color',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => trans('translations.current_account_controller.basariyla_cari_secildi')
        ]);
    }

    public function switchAccount(User $user)
    {
        if (auth('web')->check() && auth('web')->user()->role === 'salesman') {
            $this->switchUserAndApplyDiscounts($user);
        } elseif (auth('web')->check() && auth('web')->user()->role === 'dealer') {
            session()->forget([
                'cart_discount_rate_tl_1',
                'cart_discount_rate_tl_2',
                'cart_discount_rate_usd_1',
                'cart_discount_rate_usd_2',
                'cart_discount_rate_eur_1',
                'cart_discount_rate_eur_2',
                'cart_discount_rate_gbp_1',
                'cart_discount_rate_gbp_2',
                'acting_dealer_id',
                'acting_subdealer_id',
                'admin_login',
                'cart_payment_type',
                'cart_payment_type_text',
                'cart_payment_type_color',
            ]);

            auth('web')->loginUsingId($user->id);
        }

        logSession("{$user->name} adlı kullanıcı 'Diğer Hesaba Geç' kullanarak geçiş yapıldı.", null, 'info', 'login_logs');

        forget_cache_keys("group_users_{$user->group_code}");

        session()->flash('success', trans('translations.web.basariyla_username_adli_hesaba_gecis_yaptiniz', ['username' => $user->name]));

        return redirect()->back();
    }

    private function switchUserAndApplyDiscounts($user)
    {
        if (auth('web')->check() && auth('web')->user()->role === 'salesman') {
            session()->put('acting_dealer_id', $user->id);
        } elseif (auth('web')->check() && auth('web')->user()->role === 'dealer') {
            session()->put('acting_subdealer_id', $user->id);
        }

        session()->forget([
            'cart_discount_rate_tl_1',
            'cart_discount_rate_tl_2',
            'cart_discount_rate_usd_1',
            'cart_discount_rate_usd_2',
            'cart_discount_rate_eur_1',
            'cart_discount_rate_eur_2',
            'cart_discount_rate_gbp_1',
            'cart_discount_rate_gbp_2'
        ]);

        $userCurrency = Str::lower($user->currency);

        if (auth('web')->check() && auth('web')->user()->role === 'salesman') {
            if ($user->plasiyer1 == auth('web')->user()->code) {
                $user->plasiyer1_discount1 == 0 ? '' : session()->put("cart_discount_rate_{$userCurrency}_1", $user->plasiyer1_discount1);
                $user->plasiyer1_discount2 == 0 ? '' : session()->put("cart_discount_rate_{$userCurrency}_2", $user->plasiyer1_discount2);
            } else if ($user->plasiyer2 == auth('web')->user()->code) {
                $user->plasiyer2_discount1 == 0 ? '' : session()->put("cart_discount_rate_{$userCurrency}_1", $user->plasiyer2_discount1);
                $user->plasiyer2_discount2 == 0 ? '' : session()->put("cart_discount_rate_{$userCurrency}_2", $user->plasiyer2_discount2);
            }
        }
    }
}
