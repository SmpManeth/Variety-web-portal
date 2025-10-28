<?php

namespace App\Services;

use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use Throwable;

class RoleService
{
    public function getAllRoles()
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    public function getAllPermissions()
    {
        return Permission::orderBy('name')->get();
    }

    public function create(array $data): ?Role
    {
        try {
            $role = Role::create(['name' => $data['name']]);
            $role->syncPermissions($data['permissions'] ?? []);
            return $role;
        } catch (Throwable $e) {
            Log::error('❌ Failed to create role', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function update(Role $role, array $data): bool
    {
        try {
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($data['permissions'] ?? []);
            return true;
        } catch (Throwable $e) {
            Log::error('❌ Failed to update role', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function delete(Role $role): bool
    {
        try {
            $role->delete();
            return true;
        } catch (Throwable $e) {
            Log::error('❌ Failed to delete role', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
