<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:admin', ['except' => ['logout']]);
    }

    public function loginPage()
    {
        return view('backend.pages.auth.login');
    }

    public function login()
    {
        $validator = Validator::make(request()->all(),
        [
            'username' => 'required',
            'password' => 'required'
        ],
        [
            'username.required' => 'Lütfen kullanıcı adını girin.',
            'password.required' => 'Lütfen şifre girin.'
        ]);

        if ($validator->fails()) {
            $data['warning'] = $validator->errors()->first();
        } else {
            $credentials = request()->only('username', 'password');

            $user = Admin::where('username', $credentials['username'])->first();

            if ($user) {
                if ($user->block_entry == 1) {
                    $data['error'] = 'Panele erişiminiz engellendi. Lütfen daha sonra tekrar deneyiniz.';
                } elseif (Auth::guard('admin')->attempt($credentials + ['status' => 1], request('remember'))) {
                    Admin::where('username', $credentials['username'])->update([
                        'last_login_ip' => request()->ip(),
                        'last_login_date' => now()
                    ]);

                    $data['success'] = route('admin.index');

                    Log::channel('login_logs')->info("{$user->name} {$user->surname} adlı kullanıcı yönetim paneline giriş yaptı.");
                } else {
                    $data['error'] = 'Girilen bilgiler doğru değil, lütfen tekrar deneyin.';
                }
            } else {
                $data['error'] = 'Girilen bilgiler doğru değil, lütfen tekrar deneyin.';
            }
        }
        return response()->json($data);
    }

    public function logout()
    {
        auth('admin')->logout();
        return redirect()->route('admin.login.page');
    }
}
