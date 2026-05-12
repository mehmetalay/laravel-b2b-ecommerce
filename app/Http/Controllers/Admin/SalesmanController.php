<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Application\Category\Services\CategoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\SalesmanRequest;
use Illuminate\Support\Facades\Validator;

class SalesmanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = User::salesman()
            ->when($name = request()->get('name'), function ($query) use ($name) {
                $query->where(function($query) use ($name) {
                    $query->where('name', 'like', "%{$name}%")
                        ->orWhere('code', 'like', "%{$name}%")
                        ->orWhere('email', 'like', "%{$name}%")
                        ->orWhere('phone', 'like', "%{$name}%");
                });
            })
            ->orderBy('code', 'ASC')
            ->paginate(100);

        return view('backend.pages.dealers.salesmans.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.pages.dealers.salesmans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SalesmanRequest $request)
    {
        $code = $request['code'];

        $salesman = User::create([
            'current_account_id' => $request['current_account_id'],
            'name' => $request['name'],
            'code' => $code,
            'email' => $request['email'],
            'phone' => $request['phone'],
            'username' => $code,
            'password' => bcrypt($request['password']),
            'hide_category_ids' => $request['hide_category_ids'],
            'role' => 'salesman',
            'status' => $request['status'],
            'report_access' => $request['report_access'],
            'access_type' => $request['access_type'],
            'show_all_installments' => $request['show_all_installments'],
            'can_edit_price' => $request['can_edit_price'],
            'can_edit_discount' => $request['can_edit_discount'],
            'hide_all_stock_quantities' => $request['hide_all_stock_quantities']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla oluşturuldu!',
            'redirect' => route('admin.salesmans.edit', [$salesman->id])
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $salesman)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $salesman)
    {
        $categoryIds = [];
        $categories = [];

        if (!empty($salesman->hide_category_ids)) {
            $categoryIds = array_filter(explode(',', $salesman->hide_category_ids));
            $categories = app(CategoryService::class)->getAllActiveCategories()->whereIn('id', $categoryIds)->keyBy('id');
        }

        return view('backend.pages.dealers.salesmans.edit', compact('salesman', 'categoryIds', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SalesmanRequest $request, User $salesman)
    {
        $salesman->update([
            'name' => $request['name'],
            'code' => $request['code'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'username' => $request['code'],
            'password' => $request['password'] ? bcrypt($request['password']) : $salesman->password,
            'hide_category_ids' => $request['hide_category_ids'],
            'status' => $request['status'],
            'block_entry' => $request['block_entry'],
            'report_access' => $request['report_access'],
            'access_type' => $request['access_type'],
            'show_all_installments' => $request['show_all_installments'],
            'can_edit_price' => $request['can_edit_price'],
            'can_edit_discount' => $request['can_edit_discount'],
            'hide_all_stock_quantities' => $request['hide_all_stock_quantities']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla güncellendi!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $salesman)
    {
        $salesman->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }
}


