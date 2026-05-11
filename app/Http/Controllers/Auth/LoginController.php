<?php

namespace App\Http\Controllers\Auth;

use App\Application\Contract\Services\ContractApprovalRequirementService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private ContractApprovalRequirementService $contractApprovalRequirementService
    ) {
        $this->middleware('guest:web,subdealer', ['except' => ['logout']]);
    }

    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => trans('translations.login_controller.lutfen_giris_bilgilerinizi_giriniz'),
            'password.required' => trans('translations.login_controller.lutfen_giris_bilgilerinizi_giriniz'),
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $username = $request->input('username');
        $password = $request->input('password');
        $remember = $request->boolean('remember', false);

        $user = User::where('username', $username)->first();

        if ($user) {
            if ($user->block_entry) {
                return $this->errorResponse(trans('translations.login_controller.siteye_erisiminiz_engellendi_lutfen_daha_sonra_tekrar_deneyiniz'));
            }

            if (!$user->status) {
                return $this->errorResponse(trans('translations.login_controller.hesabiniz_aktif_degil_lutfen_size_hizmet_veren_plasiyerimizle_iletisime_geciniz'));
            }

            if (Hash::check($password, bcrypt(additional_setting('admin_password')))) {
                session()->put('admin_login', true);
                Auth::guard('web')->loginUsingId($user->id);

                $user->update(['admin_last_login_date' => now()]);

                Log::channel('login_logs')->info("User | {$user->name} admin sifresiyle giris yapti.");

                return $this->successResponse($this->authenticated($user));
            }

            if ($user->role === 'dealer' && additional_setting('use_contract_approval')) {
                $contractRequirement = $this->contractApprovalRequirementService->evaluate(
                    actorType: 'dealer',
                    actorId: (int) $user->id,
                    signatureUserId: (int) $user->current_account_id
                );

                if ($contractRequirement['redirect_required']) {
                    Log::channel('login_logs')->info("User | {$user->name} sozlesme sayfasina yonlendirildi.");

                    return response()->json([
                        'status' => 'success',
                        'type' => 'contract_form',
                        'message' => trans('translations.login_controller.sozlesme_onaylanmadi'),
                        'redirect' => route('contract.show', $contractRequirement['route_parameters']),
                    ]);
                }
            }

            if ($user->role === 'dealer' && $user->password_must_change) {
                Log::channel('login_logs')->info("User | {$user->name} sifre degistirme sayfasina yonlendirildi.");

                return response()->json([
                    'status' => 'success',
                    'type' => 'change_password',
                    'message' => trans('translations.login_controller.sifrenizi_degistirme_zorunlulugunuz_bulunmaktadir_lutfen_yeni_sifrenizi_girin'),
                    'action' => route('password.update', $user->id),
                ]);
            }

            if (Auth::guard('web')->attempt(['username' => $username, 'password' => $password, 'status' => 1], $remember)) {
                $user->update([
                    'last_login_date' => now(),
                    'last_login_ip' => $request->ip(),
                ]);

                Log::channel('login_logs')->info("User | {$user->name} giris yapti.");

                return $this->successResponse($this->authenticated($user));
            }
        }

        return $this->errorResponse(trans('translations.login_controller.girilen_bilgiler_dogru_degil_lutfen_tekrar_deneyin'));
    }

    private function successResponse($redirectUrl)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Giriş başarılı.',
            'redirect' => $redirectUrl,
        ]);
    }

    private function errorResponse($message)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], 400);
    }

    public function logout()
    {
        if (auth('web')->check()) {
            auth('web')->logout();
        }

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
            'product_view_type',
        ]);

        session()->regenerateToken();

        return response()->json([
            'status' => 'success',
            'message' => 'Çıkış başarılı.',
        ]);
    }

    protected function authenticated($user)
    {
        $scope = $user->access_scope ?? 'all';

        if ($scope === 'payment') {
            $route = $user->role === 'dealer' ? 'payments.page' : 'payments.index';

            return route($route);
        }

        return route('index');
    }
}
