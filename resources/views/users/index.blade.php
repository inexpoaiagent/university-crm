@extends('layouts.app')

@php($authUser = request()->attributes->get('auth_user'))
@php($allowedRoles = $manageableRoleSlugs ?? [])

@section('content')
<div class="card">
    <div class="toolbar">
        <button onclick="document.getElementById('addUser').showModal()">
            + Add {{ $authUser->role_slug === 'agent' ? 'Sub-Agent' : 'Admin/Agent/Sub-Agent' }}
        </button>
    </div>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td><a href="/agents/{{ $u->id }}">{{ $u->name }}</a></td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->role_slug }}</td>
                <td>{{ $u->is_active ? 'Yes' : 'No' }}</td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="secondary" type="button" onclick="document.getElementById('editUser{{ $u->id }}').showModal()">Edit</button>
                    @if($canManagePermissions && in_array($u->role_slug, ['admin','agent','sub_agent'], true))
                        <button class="secondary" type="button" onclick="document.getElementById('userPerm{{ $u->id }}').showModal()">Permissions</button>
                    @endif
                    @if((int)$u->id !== (int)$authUser->id)
                        <form method="POST" action="/agents/{{ $u->id }}" onsubmit="return confirm('Delete user?')">@csrf @method('DELETE')<button class="secondary">Delete</button></form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div style="margin-top:10px;">{{ $users->links() }}</div>
</div>

@if($canManagePermissions)
<div class="card" style="margin-top:12px;">
    <h3>Role Permission Manager</h3>
    <p class="footer-note">Configure module access for Admin, Agent and Sub-Agent. Example: remove <code>universities.create</code> from Agent to prevent adding universities.</p>

    @foreach(['admin' => 'Admin', 'agent' => 'Agent', 'sub_agent' => 'Sub-Agent'] as $roleSlug => $roleLabel)
        <form method="POST" action="/agents/roles/permissions" style="margin-top:12px;border:1px solid #dbeafe;border-radius:10px;padding:10px;">
            @csrf
            <input type="hidden" name="role_slug" value="{{ $roleSlug }}">
            <h4 style="margin:0 0 8px 0;">{{ $roleLabel }} Permissions</h4>
            <div class="tabs" style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach($permissions as $permission)
                    <label class="tab" style="cursor:pointer;">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->key }}"
                            {{ in_array($permission->key, $rolePermissionMap[$roleSlug] ?? [], true) ? 'checked' : '' }}>
                        {{ $permission->key }}
                    </label>
                @endforeach
            </div>
            <button type="submit" style="margin-top:10px;">Save {{ $roleLabel }} Permissions</button>
        </form>
    @endforeach
</div>
@endif

<dialog id="addUser" class="card" style="max-width:560px;">
    <h3 style="margin-top:0;">Add User</h3>
    <form method="POST" action="/agents">
        @csrf
        <input name="name" placeholder="Name" required style="width:100%;margin-bottom:8px;">
        <input name="email" placeholder="Email" type="email" required style="width:100%;margin-bottom:8px;">
        <input name="password" placeholder="Password" type="password" required style="width:100%;margin-bottom:8px;">
        <select name="role_slug" style="width:100%;margin-bottom:10px;">
            @foreach($allowedRoles as $roleSlug)
                <option value="{{ $roleSlug }}">{{ strtoupper($roleSlug) }}</option>
            @endforeach
        </select>
        <button type="submit">Create user</button>
    </form>
</dialog>

@foreach($users as $u)
<dialog id="editUser{{ $u->id }}" class="card" style="max-width:560px;">
    <h3 style="margin-top:0;">Edit User</h3>
    <form method="POST" action="/agents/{{ $u->id }}">
        @csrf
        @method('PUT')
        <input name="name" value="{{ $u->name }}" required style="width:100%;margin-bottom:8px;">
        <input name="email" type="email" value="{{ $u->email }}" required style="width:100%;margin-bottom:8px;">
        <input name="password" type="password" placeholder="Leave blank to keep password" style="width:100%;margin-bottom:8px;">
        <select name="role_slug" style="width:100%;margin-bottom:8px;">
            @foreach($allowedRoles as $roleSlug)
                <option value="{{ $roleSlug }}" {{ $u->role_slug === $roleSlug ? 'selected' : '' }}>{{ strtoupper($roleSlug) }}</option>
            @endforeach
        </select>
        <select name="is_active" style="width:100%;margin-bottom:8px;">
            <option value="1" {{ (int) $u->is_active === 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ (int) $u->is_active === 0 ? 'selected' : '' }}>Inactive</option>
        </select>
        <div style="display:flex;gap:8px;">
            <button type="submit">Save</button>
            <button type="button" class="secondary" onclick="document.getElementById('editUser{{ $u->id }}').close()">Cancel</button>
        </div>
    </form>
</dialog>

@if($canManagePermissions && in_array($u->role_slug, ['admin','agent','sub_agent'], true))
<dialog id="userPerm{{ $u->id }}" class="card" style="max-width:760px;">
    <h3 style="margin-top:0;">User Permission Overrides: {{ $u->name }}</h3>
    <p class="footer-note">These overrides take priority over role permissions. Use Allow or Deny per permission.</p>
    <form method="POST" action="/agents/{{ $u->id }}/permissions">
        @csrf
        <div class="tabs" style="display:flex;flex-wrap:wrap;gap:8px;">
            @foreach($permissions as $permission)
                @php($state = $userPermissionMap[$u->id][$permission->key] ?? '')
                <div class="tab" style="display:flex;flex-direction:column;gap:6px;min-width:220px;">
                    <strong style="font-size:12px;">{{ $permission->key }}</strong>
                    <label style="font-size:12px;">
                        <input type="checkbox" name="allow[]" value="{{ $permission->key }}" {{ $state === 'allow' ? 'checked' : '' }}>
                        Allow
                    </label>
                    <label style="font-size:12px;">
                        <input type="checkbox" name="deny[]" value="{{ $permission->key }}" {{ $state === 'deny' ? 'checked' : '' }}>
                        Deny
                    </label>
                </div>
            @endforeach
        </div>
        <div style="display:flex;gap:8px;margin-top:10px;">
            <button type="submit">Save Overrides</button>
            <button type="button" class="secondary" onclick="document.getElementById('userPerm{{ $u->id }}').close()">Cancel</button>
        </div>
    </form>
</dialog>
@endif
@endforeach
@endsection
