<?php

namespace App\Http\Controllers;

use App\Beer;
use App\BeerPhoto;
use App\Http\Requests\BeerRequest;
use App\Traits\FileHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeerController extends Controller
{
    use FileHandler;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getBeers(): JsonResponse
    {
        $beers = Beer::with('photos')->paginate(10);
        return response()->json(['success', $beers]);
    }

    public function store(BeerRequest $request): JsonResponse
    {
        $user = auth()->id();
        $data = $request->validated();
        $data['create_user_id'] = auth()->id();

        try {
            DB::beginTransaction();
            $newBeer = new Beer();
            $newBeer->name = [
                'me' => $data['names'][0],
                'en' => $data['names'][1]
            ];
            $newBeer->description = [
                'me' => $data['descriptions'][0],
                'en' => $data['descriptions'][1]
            ];
            $newBeer->video_link = $data['video_link'];
            $newBeer->create_user_id = $user;
            $newBeer->save();

            foreach ($data['photos'] as $photo) {
                $newBeerPhoto = new BeerPhoto();
                $newBeerPhoto->photo_path = self::storeFileFromBase64($photo, 'beers');
                $newBeerPhoto->beer_id = $newBeer->id;
                $newBeerPhoto->create_user_id = $user;
                $newBeerPhoto->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 500);
        }

        return response()->json(['success']);
    }

    public function update(BeerRequest $request, Beer $beer): JsonResponse
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
                    $photo = BeerPhoto::findOrFail($deletePhoto);
                    $photo->delete();
                }
            }

            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $newBeerPhoto = new BeerPhoto();
                    $newBeerPhoto->photo_path = self::storeFileFromBase64($photo, 'beers');
                    $newBeerPhoto->beer_id = $beer->id;
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
        Beer::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function searchBeers($keyword): JsonResponse
    {
        return response()->json(['success', Beer::searchBeers($keyword)]);
    }
}
