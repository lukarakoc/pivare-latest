<?php

namespace App\Http\Controllers;

use App\Permission;
use App\User;
use App\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function getUserPermissionsByUser($id): JsonResponse
    {
        $permissions = UserPermission::query()
            ->with('permission')
            ->where('user_id', '=', $id)
            ->get();
        return response()->json(['success', $permissions]);
    }

    public function getAllPermissions(): JsonResponse
    {
        return response()->json(['success', Permission::all()]);
    }

    public function savePermissions(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user);
        foreach ($request->permissions as $key => $permission) {
            $selectedPermission = UserPermission::query()
                ->where('permission_id', '=', $permission)
                ->where('user_id', '=', $user->id)
                ->first();
            if (!is_null($selectedPermission)) {
                ;
                $selectedPermission->permission_value = $request->permissionsData[$key] == 'true';
                $selectedPermission->update_user_id = auth()->id();
                $selectedPermission->save();
            } else {
                $newPermission = new UserPermission();
                $newPermission->user()->associate($user);
                $newPermission->permission_id = $permission;
                $newPermission->createUser()->associate(auth()->user());
                $newPermission->permission_value = $request->permissionsData[$key] == 'true';
                $newPermission->save();
            }
        }
        return response()->json(['success']);
    }
}
