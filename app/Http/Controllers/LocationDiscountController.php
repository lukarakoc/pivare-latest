<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationDiscountRequest;
use App\Location;
use App\LocationDiscount;
use App\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LocationDiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getDiscountsByLocation($id): JsonResponse
    {
        return response()->json(['success', LocationDiscount::where('location_id', $id)->get()]);
    }

    public function store(LocationDiscountRequest $request): JsonResponse
    {
        $data = $request->validated();
//        dd($data['location']);
//        $this->checkPermission($data['location']);
        try {
            DB::beginTransaction();
            LocationDiscount::create([
                'name' => [
                    'me' => $data['names'][0],
                    'en' => $data['names'][1]
                ],
                'description' => [
                    'me' => $data['descriptions'][0],
                    'en' => $data['descriptions'][1]
                ],
                'amount' => $data['amount'],
                'location_id' => $data['location'],
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to'],
                'create_user_id' => auth()->id()
            ]);
            DB::commit();
            return response()->json(['success'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
    }

    public function update(LocationDiscountRequest $request, LocationDiscount $discount): JsonResponse
    {
        $data = $request->validated();
//        $this->checkPermission($data['location']);
        try {
            DB::beginTransaction();
            $discount->update([
                'name' => [
                    'me' => $data['names'][0],
                    'en' => $data['names'][1]
                ],
                'description' => [
                    'me' => $data['descriptions'][0],
                    'en' => $data['descriptions'][1]
                ],
                'amount' => $data['amount'],
                'location_id' => $data['location'],
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to'],
                'update_user_id' => auth()->id()
            ]);
            DB::commit();
            return response()->json(['success'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
    }

    public function destroy($id): JsonResponse
    {
        $locationDiscount = LocationDiscount::findOrFail($id);
        $this->checkPermission($locationDiscount->location_id);
        $locationDiscount->delete();
        return response()->json(['success']);
    }

    private function checkPermission(string $locationId): JsonResponse
    {
        $user = auth()->user();
        $location = Location::findOrFail($locationId);
        if ($user->role_id == Role::EMPLOYEE || $location->owner_id != $user->id) {
            return response()->json(['Nemate prava da upravljate popustima'], 403);
        }
        dd($user);
    }
}
