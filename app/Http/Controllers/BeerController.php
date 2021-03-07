<?php

namespace App\Http\Controllers;

use App\Beer;
use App\BeerPhoto;
use App\Http\Requests\BeerRequest;
use App\Traits\FileHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeerController extends Controller
{
    use FileHandler;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getBeers(): JsonResponse
    {
        $beers = Beer::paginate(10);
        return response()->json(['success', $beers]);
    }

    public function store(BeerRequest $request): JsonResponse
    {
        $user = auth()->id();
        $data = $request->validated();
        $data['create_user_id'] = auth()->id();

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

        return response()->json(['success']);
    }

    public function update()
    {

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
