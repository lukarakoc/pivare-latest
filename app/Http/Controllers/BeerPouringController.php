<?php

namespace App\Http\Controllers;

use App\BeerPouringTutorial;
use App\BeerPouringTutorialPhoto;
use App\Http\Requests\BeerPouringRequest;
use App\Traits\FileHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BeerPouringController extends Controller
{

    use FileHandler;

    public function getBeerPhotos(): JsonResponse
    {
        $beers = BeerPouringTutorial::with('photos')->paginate(10);
        return response()->json(['success', $beers]);
    }


    public function store(BeerPouringRequest $request): JsonResponse
    {
        $user = auth()->id();
        $data = $request->validated();
        $data['create_user_id'] = auth()->id();

        try {
            DB::beginTransaction();
            $beerTutorial = new BeerPouringTutorial();
            $beerTutorial->name = [
                'me' => $data['names'][0],
                'en' => $data['names'][1]
            ];
            $beerTutorial->description = [
                'me' => $data['descriptions'][0],
                'en' => $data['descriptions'][1]
            ];
            $beerTutorial->video_link = $data['video_link'];
            $beerTutorial->create_user_id = $user;
            $beerTutorial->save();

            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $newBeerPhoto = new BeerPouringTutorialPhoto();
                    $newBeerPhoto->photo_path = self::storeFileFromBase64($photo, 'beer-pouring-tutorials');
                    $newBeerPhoto->beer_pouring_tutorial_id = $beerTutorial->id;
                    $newBeerPhoto->create_user_id = $user;
                    $newBeerPhoto->save();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 500);
        }

        return response()->json(['success']);
    }

    public function update(BeerPouringRequest $request, BeerPouringTutorial $beer): JsonResponse
    {
        $user = auth()->id();
        $data = $request->validated();
        $data['update_user_id'] = auth()->id();

        try {
            DB::beginTransaction();
            $beer->name = [
                'me' => $data['names'][0],
                'en' => $data['names'][1]
            ];
            $beer->description = [
                'me' => $data['descriptions'][0],
                'en' => $data['descriptions'][1]
            ];
            $beer->video_link = $data['video_link'];
            $beer->update_user_id = $user;
            $beer->save();

            if (isset($data['delete_photos'])) {
                foreach ($data['delete_photos'] as $deletePhoto) {
                    $photo = BeerPouringTutorialPhoto::findOrFail($deletePhoto);
                    $photo->delete();
                }
            }


            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $newBeerPhoto = new BeerPouringTutorialPhoto();
                    $newBeerPhoto->photo_path = self::storeFileFromBase64($photo, 'beer-pouring-tutorials');
                    $newBeerPhoto->beer_pouring_tutorial_id = $beer->id;
                    $newBeerPhoto->create_user_id = $user;
                    $newBeerPhoto->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 500);
        }

        return response()->json(['success']);
    }

    public function destroy($id): JsonResponse
    {
        BeerPouringTutorial::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function searchBeerPouringTutorial($keyword): JsonResponse
    {
        return response()->json(['success', BeerPouringTutorial::searchBeerTutorials($keyword)]);
    }

}

