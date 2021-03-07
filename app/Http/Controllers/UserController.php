<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Role;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getUsers(): JsonResponse
    {
        $users = User::with('role')->paginate(15);
        return response()->json(['success', $users]);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            User::create($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, $e->getTraceAsString());
        }
        return response()->json(['success']);
    }

    public function update(UserRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->id);
        try {
            DB::beginTransaction();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            if ($request->role_id != $user->role_id) {
                $user->role_id = Role::where('name', $request->role)->first()->id;
            }
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, $e->getTraceAsString());
        }
        return response()->json(['success']);
    }

    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        if ($user->id == auth()->id()) {
            return response()->json(['error' => 'Nije dozvoljeno brisanje sopstvenog profila.'], 422);
        }
        $user->delete();
        return response()->json(['success']);
    }

    public function searchUsers($keyword): JsonResponse
    {
        return response()->json(['success', User::searchUsers($keyword)]);
    }

    public function getAllRoles(): JsonResponse
    {
        return response()->json(['success', Role::all()]);
    }
}
