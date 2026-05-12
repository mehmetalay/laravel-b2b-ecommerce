<?php

namespace App\Http\Controllers\Admin;

use App\Application\Category\Services\CategoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CurrentAccountUpdateRequest;
use App\Models\User;
use App\Services\DealerService;
use App\Services\ERP\AccountImportService;
use Illuminate\Http\Request;

class CurrentAccountController extends Controller
{
    protected $service;

    public function __construct(DealerService $service)
    {
        $this->middleware('auth:admin');
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = User::customer()
            ->when($name = request()->get('name'), function ($query) use ($name) {
                $query->where(function ($query) use ($name) {
                    $query->where('name', 'like', "%{$name}%")
                        ->orWhere('code', 'like', "%{$name}%")
                        ->orWhere('email', 'like', "%{$name}%")
                        ->orWhere('province', 'like', "%{$name}%")
                        ->orWhere('district', 'like', "%{$name}%");
                });
            })
            ->orderBy('name', 'ASC')
            ->paginate(100);

        return view('backend.pages.dealers.current-accounts.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $current_account)
    {
        $categoryIds = [];
        $categories = [];

        if (!empty($current_account->hide_category_ids)) {
            $categoryIds = array_filter(explode(',', $current_account->hide_category_ids));
            $categories = app(CategoryService::class)->getAllActiveCategories()->whereIn('id', $categoryIds)->keyBy('id');
        }

        return view('backend.pages.dealers.current-accounts.edit', compact('current_account', 'categoryIds', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CurrentAccountUpdateRequest $request, User $current_account)
    {
        $request->validated();

        $this->service->update($request, $current_account);

        return response()->json([
            'status' => 'success',
            'message' => 'Basariyla guncellendi!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function import(AccountImportService $service)
    {
        ignore_user_abort(true);
        set_time_limit(0);

        response()->json([
            'status' => 'success',
            'message' => 'Cari listesi ice aktarma islemi baslatildi.',
        ])->send();

        $service->import();
    }
}
