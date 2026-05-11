<?php

namespace App\Http\Controllers\Auth\SubDealer;

use App\Models\User;
use App\Models\SubDealer;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMail;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:web,subdealer');
    }

    public function updatePassword(SubDealer $subDealer)
    {
        $validator = Validator::make(request()->all(), [
            'new_password' => [
                'required',
                'min:5',
                'regex:/^[0-9]+$/',
            ],
            'confirm_password' => 'required|same:new_password',
        ],
        [
            'new_password.required' => trans('translations.password_controller.lutfen_yeni_sifre_giriniz'),
            'new_password.min' => trans('translations.password_controller.sifre_en_az_5_karakter_olmalidir'),
            'new_password.regex' => trans('translations.password_controller.sifre_yalnizca_rakamlardan_olusmalidir'),
            'confirm_password.required' => trans('translations.password_controller.lutfen_yeni_sifre_tekrari_giriniz'),
            'confirm_password.same' => trans('translations.password_controller.yeni_sifre_ve_sifre_tekrari_eslesmiyor'),
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $subDealer->password = bcrypt($data['new_password']);
        $subDealer->password_must_change = false;
        $subDealer->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Şifre başarıyla güncellendi.',
        ]);
    }

    public function forgotPasswordForm()
    {
        return view('auth.sub-dealers.forgot-password');
    }

    public function forgotPassword()
    {
        $validator = Validator::make(request()->all(), [
            'dealer_code' => [
                'required',
                Rule::exists('users', 'dealer_code') // users tablosunda kontrol edilecek
            ],
            'username' => ['required'], // username için custom kontrol yapacağız
        ], [
            'dealer_code.required' => trans('translations.password_controller.lutfen_bayi_kodunu_giriniz'),
            'dealer_code.exists' => trans('translations.password_controller.bu_bayi_kodu_sistemde_bulunamadi'),
            'username.required' => trans('translations.password_controller.lutfen_kullanici_adini_giriniz'),
        ]);

        $validator->after(function ($validator) {
            $data = request()->all();

            if (isset($data['dealer_code'], $data['username'])) {
                $user = User::where('dealer_code', $data['dealer_code'])->first();

                if (!$user) {
                    return;
                }

                $subDealerExists = SubDealer::where('username', $data['username'])
                    ->where('dealer_id', $user->current_account_id)
                    ->exists();

                if (!$subDealerExists) {
                    $validator->errors()->add(
                        'username',
                        trans('translations.password_controller.bu_kullanici_adi_sistemde_bulunamadi')
                    );
                }
            }
        });

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $user = User::where('dealer_code', $data['dealer_code'])->first();
        $subDealer = SubDealer::where('username', $data['username'])->where('dealer_id', $user->current_account_id)->first();

        if ($subDealer->password_reset_expires_at && $subDealer->password_reset_expires_at->isFuture()) {
            $remainingMinutes = now()->diffInMinutes($subDealer->password_reset_expires_at);

            return response()->json([
                'status' => 'warning',
                'message' => trans('translations.password_controller.sifre_degistirme_linki_daha_once_gonderildi', ['remainingMinutes' => $remainingMinutes]),
            ]);
        }

        $resetCode = Str::random(64);

        $subDealer->update([
            'password_reset_code' => $resetCode,
            'password_reset_expires_at' => now()->addHour(),
        ]);

        $resetLink = route('sub-dealer.password.reset.form', ['code' => $resetCode]);

        Mail::to($subDealer->email)->send(new PasswordResetMail($subDealer, $resetLink));

        return response()->json([
            'status' => 'success',
            'message' => 'Şifre sıfırlama bağlantısı gönderildi.',
            'redirect' => route('sub-dealer.login.form'),
        ]);
    }

    public function resetPasswordForm($code)
    {
        $subDealer = SubDealer::where('password_reset_code', $code)
            ->where('password_reset_expires_at', '>', now())
            ->first();

        if (!$subDealer) {
            return redirect()->route('sub-dealer.login.form')->withErrors(['error' => trans('translations.password_controller.sifre_sifirlama_kodu_gecersiz_veya_suresi_dolmus')]);
        }

        return view('auth.sub-dealers.reset-password', ['resetCode' => $code]);
    }

    public function resetPassword($code)
    {
        $validator = Validator::make(request()->all(), [
            'new_password' => [
                'required',
                'min:5',
                'regex:/^[0-9]+$/',
            ],
            'confirm_password' => 'required|same:new_password',
        ],
        [
            'new_password.required' => trans('translations.password_controller.lutfen_yeni_sifre_giriniz'),
            'new_password.min' => trans('translations.password_controller.sifre_en_az_5_karakter_olmalidir'),
            'new_password.regex' => trans('translations.password_controller.sifre_yalnizca_rakamlardan_olusmalidir'),
            'confirm_password.required' => trans('translations.password_controller.lutfen_yeni_sifre_tekrari_giriniz'),
            'confirm_password.same' => trans('translations.password_controller.yeni_sifre_ve_sifre_tekrari_eslesmiyor'),
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $subDealer = SubDealer::where('password_reset_code', $code)->first();

        $subDealer->update([
            'password' => bcrypt($data['new_password']),
            'password_must_change' => 0,
            'password_reset_code' => null,
            'password_reset_expires_at' => null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Şifre başarıyla güncellendi.',
            'redirect' => route('sub-dealer.login.form'),
        ]);
    }
}
