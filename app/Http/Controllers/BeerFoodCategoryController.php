<?php

namespace App\Http\Controllers;

use App\BeerFoodCategory;
use App\Http\Requests\BeerFoodCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BeerFoodCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getBeerFoodCategories(): JsonResponse
    {
        return response()->json(['success', BeerFoodCategory::paginate(10)]);
    }

    public function getAllBeerFoodCategories(): JsonResponse
    {
        return response()->json(['success', BeerFoodCategory::select('id', 'name')->get()]);
    }

    public function storeBeerFoodCategory(BeerFoodCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            BeerFoodCategory::create([
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

    public function updateBeerFoodCategory(BeerFoodCategoryRequest $request, BeerFoodCategory $beerFoodCategory): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $beerFoodCategory->update([
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

    public function destroyBeerFoodCategory($id): JsonResponse
    {
        BeerFoodCategory::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function searchBeerFoodCategories($keyword): JsonResponse
    {
        return response()->json(['success', BeerFoodCategory::searchCategories($keyword)]);
    }
}
