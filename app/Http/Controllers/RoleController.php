<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30|regex:/^[A-Za-z0-9_]+$/|unique:roles,name',
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ], [
            'name.required' => 'Kode role wajib diisi.',
            'name.unique' => 'Kode role ini sudah digunakan.',
            'name.regex' => 'Kode role hanya boleh memuat huruf kapital, angka, dan garis bawah (_).',
            'label.required' => 'Nama jabatan / label role wajib diisi.',
        ]);

        $roleCode = strtoupper(trim($validated['name']));
        $permissions = array_values(array_filter($validated['permissions'] ?? []));

        try {
            $role = DB::transaction(function () use ($roleCode, $validated, $permissions) {
                $role = Role::create([
                    'name' => $roleCode,
                    'label' => trim($validated['label']),
                    'description' => $validated['description'] ? trim($validated['description']) : null,
                    'permissions' => $permissions,
                    'is_system' => false,
                ]);

                AuditLog::log('CREATE_ROLE', Role::class, $role->id, null, [
                    'name' => $role->name,
                    'label' => $role->label,
                    'permissions_count' => count($permissions),
                ]);

                return $role;
            });

            return redirect()->route('settings.index', ['tab' => 'roles'])
                ->with('success', "Role {$role->label} ({$role->name}) berhasil ditambahkan dengan " . count($permissions) . " hak akses.");
        } catch (Exception $e) {
            return redirect()->route('settings.index', ['tab' => 'roles'])
                ->withInput()
                ->with('error', 'Gagal menambahkan role: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified role and its permissions.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ], [
            'label.required' => 'Nama jabatan / label role wajib diisi.',
        ]);

        $permissions = array_values(array_filter($validated['permissions'] ?? []));

        // If it's ADMIN, preserve full wildcard access unless explicitly customized
        if ($role->name === 'ADMIN' && empty($permissions)) {
            $permissions = ['*'];
        }

        try {
            DB::transaction(function () use ($role, $validated, $permissions) {
                $oldValues = [
                    'label' => $role->label,
                    'description' => $role->description,
                    'permissions' => $role->permissions,
                ];

                $role->update([
                    'label' => trim($validated['label']),
                    'description' => $validated['description'] ? trim($validated['description']) : null,
                    'permissions' => $permissions,
                ]);

                AuditLog::log('UPDATE_ROLE_PERMISSIONS', Role::class, $role->id, $oldValues, [
                    'label' => $role->label,
                    'permissions_count' => count($permissions),
                ]);
            });

            return redirect()->route('settings.index', ['tab' => 'roles'])
                ->with('success', "Hak akses role {$role->label} ({$role->name}) berhasil diperbarui.");
        } catch (Exception $e) {
            return redirect()->route('settings.index', ['tab' => 'roles'])
                ->withInput()
                ->with('error', 'Gagal memperbarui hak akses: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->isProtected()) {
            return redirect()->route('settings.index', ['tab' => 'roles'])
                ->with('error', "Role sistem utama ({$role->name}) dilindungi dan tidak dapat dihapus demi keamanan operasional.");
        }

        $assignedUsersCount = User::where('role', $role->name)->count();
        if ($assignedUsersCount > 0) {
            return redirect()->route('settings.index', ['tab' => 'roles'])
                ->with('error', "Role {$role->label} ({$role->name}) tidak dapat dihapus karena masih digunakan oleh {$assignedUsersCount} akun pengguna.");
        }

        try {
            $code = $role->name;
            $label = $role->label;
            $id = $role->id;

            DB::transaction(function () use ($role, $id, $code, $label) {
                $role->delete();
                AuditLog::log('DELETE_ROLE', Role::class, $id, ['name' => $code, 'label' => $label]);
            });

            return redirect()->route('settings.index', ['tab' => 'roles'])
                ->with('success', "Role {$label} ({$code}) berhasil dihapus dari sistem.");
        } catch (Exception $e) {
            return redirect()->route('settings.index', ['tab' => 'roles'])
                ->with('error', 'Gagal menghapus role: ' . $e->getMessage());
        }
    }
}
