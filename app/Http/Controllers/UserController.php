<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->authUser($request);
        $usersQuery = User::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->whereNull('deleted_at');
        if ($user->role_slug === 'agent' && $this->hasParentUserColumn()) {
            $usersQuery->where(function ($query) use ($user) {
                $query->where('id', $user->id)
                    ->orWhere('parent_user_id', $user->id);
            });
        }
        $users = $usersQuery->latest('id')->paginate(15);

        $roles = Role::query()->where(function ($q) use ($user) {
            $q->where('tenant_id', $user->tenant_id)->orWhere('is_system', 1);
        })->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('group_key')->orderBy('name')->get();
        $rolePermissionMap = DB::table('role_permissions')
            ->join('roles', 'roles.id', '=', 'role_permissions.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->select('roles.slug as role_slug', 'permissions.key as permission_key')
            ->get()
            ->groupBy('role_slug')
            ->map(fn ($rows) => $rows->pluck('permission_key')->values()->all())
            ->all();

        $userPermissionMap = [];
        if (Schema::hasTable('user_permissions')) {
            $userPermissionMap = DB::table('user_permissions')
                ->whereIn('user_id', $users->pluck('id'))
                ->orderByDesc('id')
                ->get(['user_id', 'permission_key', 'is_allowed'])
                ->groupBy('user_id')
                ->map(function ($rows) {
                    $map = [];
                    foreach ($rows as $row) {
                        if (!array_key_exists($row->permission_key, $map)) {
                            $map[$row->permission_key] = ((int) $row->is_allowed === 1) ? 'allow' : 'deny';
                        }
                    }
                    return $map;
                })
                ->all();
        }
        $manageableRoleSlugs = $this->manageableRoleSlugs($user);
        $canManagePermissions = $user->role_slug === 'super_admin';

        return view(
            'users.index',
            compact('users', 'roles', 'permissions', 'rolePermissionMap', 'userPermissionMap', 'manageableRoleSlugs', 'canManagePermissions')
        );
    }

    public function show(Request $request, int $id): View
    {
        $auth = $this->authUser($request);
        $user = User::query()->forTenant($auth->tenant_id, $auth->role_slug)->whereNull('deleted_at')->findOrFail($id);
        $students = collect();
        if (in_array($user->role_slug, ['agent', 'sub_agent'], true)) {
            $students = Student::query()
                ->forTenant($auth->tenant_id, $auth->role_slug)
                ->whereNull('deleted_at')
                ->when($user->role_slug === 'agent', fn ($q) => $q->where('agent_id', $user->id))
                ->when($user->role_slug === 'sub_agent', fn ($q) => $q->where('sub_agent_id', $user->id))
                ->latest('id')
                ->limit(50)
                ->get(['id', 'full_name', 'email', 'stage', 'agent_id', 'sub_agent_id']);
        }
        return view('users.show', compact('user', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $auth = $this->authUser($request);
        $roleValidation = implode(',', $this->manageableRoleSlugs($auth));
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => [
                'required',
                'email',
                'max:120',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => 'required|string|min:8',
            'role_slug' => 'required|string|in:' . $roleValidation,
        ]);
        if ($auth->role_slug !== 'super_admin' && $data['role_slug'] === 'super_admin') {
            return back()->withErrors(['role_slug' => 'Only super admin can create super admin users.']);
        }
        $data['name'] = trim((string) $data['name']);
        $data['email'] = mb_strtolower(trim((string) $data['email']));
        $data['tenant_id'] = $auth->tenant_id;
        $data['language'] = 'en';
        $data['is_active'] = 1;
        $data['password'] = Hash::make($data['password']);
        if ($this->hasParentUserColumn()) {
            $data['parent_user_id'] = $auth->role_slug === 'agent' ? $auth->id : null;
        }
        $user = User::query()->create($data);
        $this->audit($request, 'user.create', 'user', $user->id, ['role' => $data['role_slug']]);

        return back()->with('success', 'User created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $auth = $this->authUser($request);
        $user = User::query()->forTenant($auth->tenant_id, $auth->role_slug)->whereNull('deleted_at')->findOrFail($id);
        if ($auth->role_slug === 'agent' && $this->hasParentUserColumn()) {
            if ((int) $user->id !== (int) $auth->id && (int) ($user->parent_user_id ?? 0) !== (int) $auth->id) {
                abort(403, 'You can only manage your own account and your sub-agents.');
            }
        }
        $roleValidation = implode(',', $this->manageableRoleSlugs($auth));
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => [
                'required',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at'),
            ],
            'role_slug' => 'required|string|in:' . $roleValidation,
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:8',
        ]);
        if ($auth->role_slug !== 'super_admin' && $data['role_slug'] === 'super_admin') {
            return back()->withErrors(['role_slug' => 'Only super admin can assign super admin role.']);
        }
        $data['name'] = trim((string) $data['name']);
        $data['email'] = mb_strtolower(trim((string) $data['email']));
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        $this->audit($request, 'user.update', 'user', $user->id, $data);

        return back()->with('success', 'User updated.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $auth = $this->authUser($request);
        $user = User::query()->forTenant($auth->tenant_id, $auth->role_slug)->whereNull('deleted_at')->findOrFail($id);
        if ($auth->id === $user->id) {
            return back()->withErrors(['email' => 'You cannot delete your own account.']);
        }
        if ($auth->role_slug === 'agent' && $this->hasParentUserColumn()) {
            if ((int) ($user->parent_user_id ?? 0) !== (int) $auth->id) {
                abort(403, 'You can only delete your own sub-agents.');
            }
        }
        $user->update(['deleted_at' => now()]);
        $this->audit($request, 'user.delete', 'user', $id);

        return back()->with('success', 'User deleted.');
    }

    public function updateRolePermissions(Request $request): RedirectResponse
    {
        $auth = $this->authUser($request);
        if ($auth->role_slug !== 'super_admin') {
            abort(403, 'Only super admin can update role permissions.');
        }

        $data = $request->validate([
            'role_slug' => 'required|string|in:admin,agent,sub_agent',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,key',
        ]);

        $role = Role::query()->where('slug', $data['role_slug'])->firstOrFail();
        $permissionIds = Permission::query()->whereIn('key', $data['permissions'] ?? [])->pluck('id')->all();
        DB::transaction(function () use ($role, $permissionIds): void {
            DB::table('role_permissions')->where('role_id', $role->id)->delete();
            if (!empty($permissionIds)) {
                $rows = array_map(fn ($permissionId) => [
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                ], $permissionIds);
                DB::table('role_permissions')->insert($rows);
            }
        });

        return back()->with('success', 'Role permissions updated.');
    }

    public function updateUserPermissions(Request $request, int $id): RedirectResponse
    {
        $auth = $this->authUser($request);
        if ($auth->role_slug !== 'super_admin') {
            abort(403, 'Only super admin can update user permissions.');
        }
        if (!Schema::hasTable('user_permissions')) {
            return back()->withErrors(['permissions' => 'user_permissions table is missing. Apply SQL patch first.']);
        }

        $target = User::query()->forTenant($auth->tenant_id, $auth->role_slug)->whereNull('deleted_at')->findOrFail($id);
        if (!in_array($target->role_slug, ['admin', 'agent', 'sub_agent'], true)) {
            return back()->withErrors(['permissions' => 'Only admin/agent/sub-agent overrides are supported.']);
        }

        $data = $request->validate([
            'allow' => 'nullable|array',
            'allow.*' => 'string|exists:permissions,key',
            'deny' => 'nullable|array',
            'deny.*' => 'string|exists:permissions,key',
        ]);

        $allow = array_values(array_unique($data['allow'] ?? []));
        $deny = array_values(array_unique($data['deny'] ?? []));
        $deny = array_values(array_diff($deny, $allow));

        DB::table('user_permissions')->where('user_id', $target->id)->delete();
        $rows = [];
        foreach ($allow as $key) {
            $rows[] = ['user_id' => $target->id, 'permission_key' => $key, 'is_allowed' => 1, 'created_at' => now(), 'updated_at' => now()];
        }
        foreach ($deny as $key) {
            $rows[] = ['user_id' => $target->id, 'permission_key' => $key, 'is_allowed' => 0, 'created_at' => now(), 'updated_at' => now()];
        }
        if (!empty($rows)) {
            DB::table('user_permissions')->insert($rows);
        }

        return back()->with('success', 'User-specific permissions updated.');
    }

    private function manageableRoleSlugs(User $user): array
    {
        return match ($user->role_slug) {
            'super_admin' => ['admin', 'agent', 'sub_agent', 'student'],
            'admin' => ['agent', 'sub_agent', 'student'],
            'agent' => ['sub_agent'],
            default => [],
        };
    }

    private function hasParentUserColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('users', 'parent_user_id');
        }

        return (bool) $hasColumn;
    }
}
