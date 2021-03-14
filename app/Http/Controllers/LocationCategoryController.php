<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationCategoryRequest;
use App\Location;
use App\LocationCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LocationCategoryController extends Controller
{
    public function getCategories(): JsonResponse
    {
        return response()->json(['success', LocationCategory::paginate(10)]);
    }

    public function getAllCategories(): JsonResponse
    {
        return response()->json(['success', LocationCategory::select('id', 'name')->get()]);
    }

    public function store(LocationCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            LocationCategory::create([
                'name' => [
                    'me' => $data['names'][0],
                    'en' => $data['names'][1]
                ],
                'description' => [
                    'me' => $data['descriptions'][0],
                    'en' => $data['descriptions'][1]
                ],
                'create_user_id' => auth()->id()
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
        return response()->json(['success']);
    }

    public function update(LocationCategoryRequest $request, LocationCategory $locationCategory): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $locationCategory->update([
                'name' => [
                    'me' => $data['names'][0],
                    'en' => $data['names'][1]
                ],
                'description' => [
                    'me' => $data['descriptions'][0],
                    'en' => $data['descriptions'][1]
                ],
                'update_user_id' => auth()->id()
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
        return response()->json(['success']);
    }

    public function destroy($id): JsonResponse
    {
        $cat = LocationCategory::findOrFail($id);
        $locations = Location::where('location_category_id', $id)->get();
        foreach ($locations as $l) $l->delete();
        $cat->delete();
        return response()->json(['success']);
    }

    public function searchCategories($keyword): JsonResponse
    {
        return response()->json(['success', LocationCategory::searchCategories($keyword)]);
    }
}
