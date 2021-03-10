<?php

namespace App\Http\Controllers;

use App\BeerQA;
use App\BeerQACategory;
use App\Http\Requests\BeerQACategoryRequest;
use App\Http\Requests\BeerQARequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BeerQAController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getBeerQACategories(): JsonResponse
    {
        return response()->json(['success', BeerQACategory::paginate(10)]);
    }

    public function getAllBeerQACategories(): JsonResponse
    {
        return response()->json(['success', BeerQACategory::select('id', 'name')->get()]);
    }

    public function getQA(): JsonResponse
    {
        return response()->json(['success', BeerQA::with('category')->paginate(10)]);
    }

    public function storeBeerQACategories(BeerQACategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            BeerQACategory::create([
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

    public function storeQA(BeerQARequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            BeerQA::create([
                'question' => [
                    'me' => $data['questions'][0],
                    'en' => $data['questions'][1]
                ],
                'answer' => [
                    'me' => $data['answers'][0],
                    'en' => $data['answers'][1]
                ],
                'create_user_id' => auth()->id(),
                'beer_qa_category_id' => $data['category_id']
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
        return response()->json(['success']);
    }

    public function updateBeerQACategories(BeerQACategoryRequest $request, BeerQACategory $beerQACategory): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $beerQACategory->update([
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

    public function updateQA(BeerQARequest $request, BeerQA $beerQa): JsonResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $beerQa->update([
                'question' => [
                    'me' => $data['questions'][0],
                    'en' => $data['questions'][1]
                ],
                'answer' => [
                    'me' => $data['answers'][0],
                    'en' => $data['answers'][1]
                ],
                'update_user_id' => auth()->id(),
                'beer_qa_category_id' => $data['category_id']
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
        return response()->json(['success']);
    }

    public function destroyBeerQACategories($id): JsonResponse
    {
        BeerQACategory::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function destroyQA($id): JsonResponse
    {
        BeerQA::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function searchBeerQACategories($keyword): JsonResponse
    {
        return response()->json(['success', BeerQACategory::searchCategories($keyword)]);
    }

    public function searchQA($keyword): JsonResponse
    {
        return response()->json(['success', BeerQA::searchBeerQA($keyword)]);
    }
}
