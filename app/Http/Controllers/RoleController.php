<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use DB;
use Illuminate\Http\Request;
use Log;
use Throwable;

class RoleController extends Controller {
    public function index(): array {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $permissions = Permission::orderBy('id')->get();

        return [
            'status' => 0,
            'data'   => [
                'roles'       => $roles,
                'permissions' => $permissions
            ]
        ];
    }

    public function update(Request $request): array {
        $inputRoles = collect($request->input('roles'));
        $inputRoleIds = $inputRoles->whereNotNull('id')->pluck('id');
        $roles = Role::whereIn('id', $inputRoleIds)->get();
        try {
            DB::transaction(function() use ($request, &$inputRoles, $roles, $inputRoleIds) {
                foreach($inputRoles as $i => $inputRole) {
                    $role = $roles->where('id', $inputRole['id'])->first();
                    $inertMode = false;
                    if(!$role) {
                        $role = new Role();
                        $inertMode = true;
                    }
                    $role = $this->saveModel($request, $role, $i);
                    $this->syncModel($request, $role, $i);
                    if($inertMode) {
                        $inputRoleIds[] = $role->id;
                    }
                }

            });
        } catch(Throwable $e) {
            Log::info($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        $roles = Role::whereIn('id', $inputRoleIds)->get();

        return [
            'status' => 0,
            'data'   => [
                'roles' => $roles
            ]
        ];
    }

    public function saveModel(Request $request, Role $role, $i): Role {
        $role->name = $request->input("roles.$i.name");
        $role->display_name = $request->input("roles.$i.name");
        $role->save();
        return $role;
    }

    private function syncModel(Request $request, Role $role, $i): Role {
        $permissions = collect($request->input("roles.$i.permissions"));
        $role->permissions()->sync($permissions->map(function($r) {
            return $r['id'];
        }));

        return $role->load('permissions');
    }

    public function destroy($id): array {
        $role = Role::findOrFail($id);
        $role->delete();

        return [
            'status' => 0
        ];
    }
}
