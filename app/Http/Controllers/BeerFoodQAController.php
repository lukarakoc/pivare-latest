<?php

namespace App\Http\Controllers;

use App\BeerFoodQA;
use App\BeerFoodQACategory;
use App\Http\Requests\BeerFoodQARequest;
use App\Http\Requests\BeerQACategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BeerFoodQAController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getAllQA(): JsonResponse
    {
        return response()->json(['success', BeerFoodQA::with('category')->paginate(10)]);
    }

    public function store(BeerFoodQARequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            BeerFoodQA::create([
                'question' => [
                    'me' => $data['questions'][0],
                    'en' => $data['questions'][1]
                ],
                'answer' => [
                    'me' => $data['answers'][0],
                    'en' => $data['answers'][1]
                ],
                'beer_food_qa_category_id' => $data['category_id'],
                'create_user_id' => auth()->id()
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
        return response()->json(['success']);
    }

    public function update(BeerFoodQARequest $request, BeerFoodQA $beerFoodQA): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $beerFoodQA->update([
                'question' => [
                    'me' => $data['questions'][0],
                    'en' => $data['questions'][1]
                ],
                'answer' => [
                    'me' => $data['answers'][0],
                    'en' => $data['answers'][1]
                ],
                'beer_food_qa_category_id' => $data['category_id'],
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
        BeerFoodQA::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function searchQA($keyword): JsonResponse
    {
        return response()->json(['success', BeerFoodQA::searchQA($keyword)]);
    }

//    ------------------------------------------------------------------------------------------------------------------

    public function getBeerFoodQACategories(): JsonResponse
    {
        return response()->json(['success', BeerFoodQACategory::paginate(10)]);

    }

    public function getAllBeerFoodQACategories(): JsonResponse
    {
        return response()->json(['success', BeerFoodQACategory::select('id', 'name')->get()]);

    }

    public function storeBeerFoodQACategories(BeerQACategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            BeerFoodQACategory::create([
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

    public function updateBeerFoodQACategories(BeerQACategoryRequest $request, BeerFoodQACategory $beerFoodQACategory): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $beerFoodQACategory->update([
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

    public function destroyBeerFoodQACategories($id): JsonResponse
    {
        $cat = BeerFoodQACategory::findOrFail($id);
        $qas = BeerFoodQA::where('beer_food_qa_category_id', $id)->get();
        foreach ($qas as $qa) $qa->delete();
        $cat->delete();
        return response()->json(['success']);
    }

    public function searchBeerFoodQACategories($keyword): JsonResponse
    {
        return response()->json(['success', BeerFoodQACategory::searchCategories($keyword)]);

    }
}
