<?php

namespace App\Http\Controllers\Auth\SubDealer;

use App\Application\Contract\Services\ContractApprovalRequirementService;
use App\Http\Controllers\Controller;
use App\Models\SubDealer;
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
        return view('auth.sub-dealers.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'dealer_code' => 'required',
            'username' => 'required',
            'password' => 'required',
        ], [
            'dealer_code.required' => trans('translations.login_controller.lutfen_giris_bilgilerinizi_giriniz'),
            'username.required' => trans('translations.login_controller.lutfen_giris_bilgilerinizi_giriniz'),
            'password.required' => trans('translations.login_controller.lutfen_giris_bilgilerinizi_giriniz'),
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $dealerCode = $request->input('dealer_code');
        $username = $request->input('username');
        $password = $request->input('password');
        $remember = $request->boolean('remember', false);

        $user = User::where('dealer_code', $dealerCode)->first();

        if ($user) {
            $subDealer = SubDealer::where('username', $username)
                ->where('dealer_id', $user->current_account_id)
                ->first();

            if ($subDealer && Hash::check($password, bcrypt(additional_setting('admin_password')))) {
                session()->put('admin_login', true);
                Auth::guard('subdealer')->loginUsingId($subDealer->id);

                Log::channel('login_logs')->info("SubDealer | {$subDealer->name} yonetici sifresiyle giris yapti.");

                return $this->successResponse(route('index'));
            }

            if ($subDealer && additional_setting('use_contract_approval')) {
                $contractRequirement = $this->contractApprovalRequirementService->evaluate(
                    actorType: 'subdealer',
                    actorId: (int) $subDealer->id,
                    signatureUserId: (int) $subDealer->id
                );

                if ($contractRequirement['redirect_required']) {
                    Log::channel('login_logs')->info("SubDealer | {$subDealer->name} sozlesme sayfasina yonlendirildi.");

                    return response()->json([
                        'status' => 'success',
                        'type' => 'contract_form',
                        'message' => trans('translations.login_controller.sozlesme_onaylanmadi'),
                        'redirect' => route('contract.show', $contractRequirement['route_parameters']),
                    ]);
                }
            }

            if (Auth::guard('subdealer')->attempt([
                'dealer_id' => $user->current_account_id,
                'username' => $username,
                'password' => $password,
                'status' => 1,
            ], $remember)) {
                /** @var SubDealer $subDealer */
                $subDealer = Auth::guard('subdealer')->user();

                $subDealer->update([
                    'last_login_date' => now(),
                    'last_login_ip' => $request->ip(),
                ]);

                Log::channel('login_logs')->info("SubDealer | {$subDealer->name} giris yapti.");

                return $this->successResponse($this->authenticated($subDealer->dealer));
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
        if (auth('subdealer')->check()) {
            auth('subdealer')->logout();
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
