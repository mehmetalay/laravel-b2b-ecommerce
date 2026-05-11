<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web,subdealer');
    }

    public function update(Request $request)
    {
        $request->validate(
            [
                'password' => ['required', 'string', 'min:8'],
            ],
            [
                'password.required' => 'Şifre alanı zorunludur.',
                'password.string' => 'Şifre geçerli bir metin olmalıdır.',
                'password.min' => 'Şifre en az :min karakter olmalıdır.',
            ]
        );

        $user = auth('web')->check()
            ? auth('web')->user()
            : auth('subdealer')->user();

        /** @var User $user */
        /** @var SubDealer $subDealer */

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Şifre başarıyla güncellendi',
        ]);
    }
}