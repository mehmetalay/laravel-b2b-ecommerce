<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->get('search');

        $customers = User::where('role', 'dealer')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->get(['current_account_id', 'name', 'code', 'district', 'province', 'email', 'phone', 'currency', 'balance', 'currency_balance']);

        $formattedUsers = $customers->map(function($customer) {
            return [
                'current_account_id' => $customer->current_account_id,
                'id' => $customer->current_account_id,
                'name' => $customer->name,
                'code' => $customer->code,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ];
        });

        return response()->json($formattedUsers);
    }
}
