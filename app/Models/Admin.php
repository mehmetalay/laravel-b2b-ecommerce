<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $guard = 'admin';

    protected $fillable = [
        'name',
        'surname',
        'username',
        'email',
        'password',
        'status',
        'block_entry'
    ];

    protected $casts = [
        'status' => 'boolean',
        'block_entry' => 'boolean',
    ];

    public $timestamps = true;

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function hasPermission($permission)
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
}
