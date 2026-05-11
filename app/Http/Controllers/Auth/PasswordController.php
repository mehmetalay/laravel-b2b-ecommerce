<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMail;
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

    public function updatePassword(User $user)
    {
        $validator = Validator::make(request()->all(), [
            'new_password' => [
                'required',
                'min:5',
                'regex:/^[0-9]+$/',
            ],
            'confirm_password' => 'required|same:new_password',
        ], [
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

        $user->password = bcrypt($data['new_password']);
        $user->password_must_change = false;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Şifre başarıyla güncellendi.',
        ]);
    }

    public function forgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword()
    {
        $validator = Validator::make(request()->all(), [
            'username' => 'required|exists:users,username',
        ],
        [
            'username.required' => trans('translations.password_controller.lutfen_bayi_kodunu_giriniz'),
            'username.exists' => trans('translations.password_controller.bu_bayi_kodu_sistemde_bulunamadi'),
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $user = User::where('username', $data['username'])->first();

        if ($user->password_reset_expires_at && $user->password_reset_expires_at->isFuture()) {
            $remainingMinutes = now()->diffInMinutes($user->password_reset_expires_at);

            return response()->json([
                'status' => 'warning',
                'message' => trans('translations.password_controller.sifre_degistirme_linki_daha_once_gonderildi', ['remainingMinutes' => $remainingMinutes]),
            ]);
        }

        $resetCode = Str::random(64);

        $user->update([
            'password_reset_code' => $resetCode,
            'password_reset_expires_at' => now()->addHour(),
        ]);

        $resetLink = route('password.reset.form', ['code' => $resetCode]);

        Mail::to($user->email)->send(new PasswordResetMail($user, $resetLink));

        return response()->json([
            'status' => 'success',
            'message' => 'Şifre sıfırlama bağlantısı gönderildi.',
            'redirect' => route('login.form'),
        ]);
    }

    public function resetPasswordForm($code)
    {
        $user = User::where('password_reset_code', $code)
                    ->where('password_reset_expires_at', '>', now())
                    ->first();

        if (!$user) {
            return redirect()->route('login.form')->withErrors(['error' => trans('translations.password_controller.sifre_sifirlama_kodu_gecersiz_veya_suresi_dolmus')]);
        }

        return view('auth.reset-password', ['resetCode' => $code]);
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

        $user = User::where('password_reset_code', $code)->first();

        $user->update([
            'password' => bcrypt($data['new_password']),
            'password_must_change' => 0,
            'password_reset_code' => null,
            'password_reset_expires_at' => null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Şifre başarıyla güncellendi.',
            'redirect' => route('login.form'),
        ]);
    }
}
