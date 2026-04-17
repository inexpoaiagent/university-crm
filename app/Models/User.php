<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'parent_user_id',
        'name',
        'email',
        'password',
        'role_slug',
        'language',
        'is_active',
        'deleted_at',
    ];

    protected $hidden = ['password'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role_slug === 'super_admin') {
            return true;
        }

        if (Schema::hasTable('user_permissions')) {
            $override = DB::table('user_permissions')
                ->where('user_id', $this->id)
                ->where('permission_key', $permission)
                ->orderByDesc('id')
                ->first();
            if ($override) {
                return (int) $override->is_allowed === 1;
            }
        }

        $count = DB::table('role_permissions')
            ->join('roles', 'roles.id', '=', 'role_permissions.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('roles.slug', $this->role_slug)
            ->where('permissions.key', $permission)
            ->count();

        return $count > 0;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }
}
