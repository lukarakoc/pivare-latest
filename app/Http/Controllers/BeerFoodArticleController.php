<?php

namespace App\Http\Controllers;

use App\BeerFoodArticle;
use App\BeerFoodArticlePhoto;
use App\Http\Requests\BeerFoodArticleRequest;
use App\Traits\FileHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeerFoodArticleController extends Controller
{
    use FileHandler;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getAllArticles(): JsonResponse
    {
        return response()->json(['success', BeerFoodArticle::with(['category', 'photos'])->paginate(10)]);
    }

    public function store(BeerFoodArticleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->id();

        try {
            DB::beginTransaction();
            $beerFoodArticle = new BeerFoodArticle();
            $beerFoodArticle->title = [
                'me' => $data['titles'][0],
                'en' => $data['titles'][1]
            ];
            $beerFoodArticle->text = [
                'me' => $data['texts'][0],
                'en' => $data['texts'][1]
            ];
            $beerFoodArticle->video_link = $data['video_link'];
            $beerFoodArticle->beer_food_category_id = $data['category_id'];
            $beerFoodArticle->create_user_id = $user;
            $beerFoodArticle->save();

            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $newBeerPhoto = new BeerFoodArticlePhoto();
                    $newBeerPhoto->photo_path = self::storeFileFromBase64($photo, 'beer-food-articles');
                    $newBeerPhoto->beer_food_article_id = $beerFoodArticle->id;
                    $newBeerPhoto->create_user_id = $user;
                    $newBeerPhoto->save();
                }
            }
            DB::commit();
            return response()->json(['success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
    }

    public function update(BeerFoodArticleRequest $request, BeerFoodArticle $beerFoodArticle): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->id();

        try {
            DB::beginTransaction();
            $beerFoodArticle->title = [
                'me' => $data['titles'][0],
                'en' => $data['titles'][1]
            ];
            $beerFoodArticle->text = [
                'me' => $data['texts'][0],
                'en' => $data['texts'][1]
            ];
            $beerFoodArticle->video_link = $data['video_link'];
            $beerFoodArticle->beer_food_category_id = $data['category_id'];
            $beerFoodArticle->update_user_id = $user;
            $beerFoodArticle->save();

            if (isset($data['delete_photos'])) {
                foreach ($data['delete_photos'] as $deletePhoto) {
                    $photo = BeerFoodArticlePhoto::findOrFail($deletePhoto);
                    $photo->delete();
                }
            }

            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $newBeerPhoto = new BeerFoodArticlePhoto();
                    $newBeerPhoto->photo_path = self::storeFileFromBase64($photo, 'beer-food-articles');
                    $newBeerPhoto->beer_food_article_id = $beerFoodArticle->id;
                    $newBeerPhoto->create_user_id = $user;
                    $newBeerPhoto->save();
                }
            }
            DB::commit();
            return response()->json(['success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
    }

    public function destroy($id): JsonResponse
    {
        BeerFoodArticle::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function searchArticles($keyword): JsonResponse
    {
        return response()->json(['success', BeerFoodArticle::searchArticles($keyword)]);
    }
}
