<?php

namespace App\Http\Controllers;

use App\Helpers\MailService;
use App\Http\Requests\LocationRequest;
use App\Location;
use App\LocationPhoto;
use App\Role;
use App\Traits\FileHandler;
use App\User;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    use FileHandler;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getLocations(): JsonResponse
    {
        return response()->json(['success', Location::with(['category', 'photos'])->paginate(10)]);
    }

    public function getAllLocations(): JsonResponse
    {
        return response()->json(['success', Location::all()]);
    }

    public function store(LocationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        if ($user->role_id == Role::OWNER || $user->role_id == Role::EMPLOYEE) {
            return response()->json(['Nemate prava da dodajete lokaciju'], 423);
        }

        try {
            DB::beginTransaction();
            $hashRoute = env('APP_URL') . '/coupon/' . Str::random(100);
            $path = public_path('/qr-codes/' . $this->getRandom(30) . '.png');
            $location = Location::create([
                'name' => [
                    'me' => $data['names'][0],
                    'en' => $data['names'][1]
                ],
                'description' => [
                    'me' => $data['descriptions'][0],
                    'en' => $data['descriptions'][1]
                ],
                'address' => [
                    'me' => $data['addresses'][0],
                    'en' => $data['addresses'][1]
                ],
                'hash_route' => $hashRoute,
                'logo' => self::storeFileFromBase64($data['logo'], 'logos'),
                'location_category_id' => $data['category_id'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'owner_id' => $data['owner'],
                'create_user_id' => $user->id,
                'created_at' => now(),
                'qr_code' => $path
            ]);

            if (isset($data['photos'])) {
                foreach ($data['photos'] as $addPhoto) {
                    $photo = new LocationPhoto();
                    $photo->photo_path = self::storeFileFromBase64($addPhoto, 'location-photos');
                    $photo->location()->associate($location);
                    $photo->createUser()->associate($user);
                    $photo->save();
                }
            }

            $writer = new PngWriter();
            $qrCode = QrCode::create($hashRoute)
                ->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
                ->setSize(300)
                ->setMargin(10)
                ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255));
            $result = $writer->write($qrCode);
            $result->saveToFile($path);

            $owner = User::findOrFail($data['owner']);
            $emailData = [
                'email' => $owner->email,
                'password' => $owner->temporary_password,
                'link' => env('APP_URL') . '/login'
            ];

            MailService::send('Kredencijali za pristup', $emailData, $owner->email, 'mail.toceno-email', []);

            DB::commit();
            return response()->json(['success'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
    }

    public function update(LocationRequest $request, Location $location): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        if ($user->role_id == Role::EMPLOYEE) {
            return response()->json(['Nemate prava da mijenjate lokaciju'], 423);
        }

        try {
            DB::beginTransaction();
            $location->name = [
                'me' => $data['names'][0],
                'en' => $data['names'][1],
            ];
            $location->description = [
                'me' => $data['descriptions'][0],
                'en' => $data['descriptions'][1]
            ];
            $location->address = [
                'me' => $data['addresses'][0],
                'en' => $data['addresses'][1]
            ];
            $location->location_category_id = $data['category_id'];
            if (isset($data['logo'])) {
                $location->logo = self::storeFileFromBase64($data['logo'], 'logos');
            }
            $location->latitude = $data['latitude'];
            $location->longitude = $data['longitude'];
            $location->owner_id = $data['owner'];
            $location->updateUser()->associate($user);
            $location->save();

            if (isset($data['delete_photos'])) {
                foreach ($data['delete_photos'] as $deletePhoto) {
                    $photo = LocationPhoto::findOrFail($deletePhoto);
                    $photo->delete();
                }
            }


            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $newBeerPhoto = new LocationPhoto();
                    $newBeerPhoto->photo_path = self::storeFileFromBase64($photo, 'location-photos');
                    $newBeerPhoto->location()->associate($location);
                    $newBeerPhoto->createUser()->associate($user);
                    $newBeerPhoto->save();
                }
            }

            // TODO: Logika za slanje mail-a

            DB::commit();
            return response()->json(['success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 400);
        }
    }

    public function destroy($id): JsonResponse
    {
        Location::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function search($keyword): JsonResponse
    {
        return response()->json(['success', Location::searchLocations($keyword)]);
    }

    public function getRandom($number): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $number; $i++) {
            $string .= $characters[mt_rand(0, $max)];
        }
        return $string;
    }
}
