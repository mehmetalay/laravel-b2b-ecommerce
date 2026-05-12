<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Models\Admin;

class UserController extends Controller
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
        $items = Admin::when($name = request()->get('name'), function ($query) use ($name) {
                $query->where(function ($query) use ($name) {
                    $query->where('name', 'like', "%{$name}%")
                        ->orWhere('surname', 'like', "%{$name}%")
                        ->orWhere('username', 'like', "%{$name}%")
                        ->orWhere('email', 'like', "%{$name}%");
                });
            })
            ->paginate(100);

        return view('backend.pages.settings.users.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.pages.settings.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminUserRequest $request)
    {
        $admin = Admin::create([
            'name' => $request->input('name'),
            'surname' => $request->input('surname'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => bcrypt((string) $request->input('password')),
            'status' => $request->input('status'),
        ]);

        $admin->permissions()->attach($request->input('permissions', []));

        return response()->json([
            'status' => 'success',
            'message' => 'Basariyla eklendi',
            'redirect' => route('admin.settings.users.index'),
        ]);
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
    public function edit(Admin $user)
    {
        return view('backend.pages.settings.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminUserRequest $request, Admin $user)
    {
        $user->update([
            'name' => $request->input('name'),
            'surname' => $request->input('surname'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => $request->filled('password') ? bcrypt((string) $request->input('password')) : $user->password,
            'status' => $request->input('status'),
            'block_entry' => $request->input('block_entry'),
        ]);

        $user->permissions()->sync($request->input('permissions', []));

        return response()->json([
            'status' => 'success',
            'message' => 'Basariyla guncellendi',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $user)
    {
        $user->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }
}
