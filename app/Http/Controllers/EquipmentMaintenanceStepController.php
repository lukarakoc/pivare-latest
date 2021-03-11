<?php

namespace App\Http\Controllers;

use App\EquipmentMaintenanceTutorialStep;
use App\EquipmentMaintenanceTutorialStepPhoto;
use App\Http\Requests\EquipmentMaintenanceStepRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EquipmentMaintenanceStepController extends Controller
{
    public function getStepsBySection($id): JsonResponse
    {
        $steps = EquipmentMaintenanceTutorialStep::with('photos')->where('section_id', '=', $id)->paginate(10);
        return response()->json(['success', $steps]);
    }

    public function getEquipmentPhotos(): JsonResponse
    {
        $equipment = EquipmentMaintenanceTutorialStep::with('photos')->paginate(10);
        return response()->json(['success', $equipment]);
    }

    public function store(EquipmentMaintenanceStepRequest $request): JsonResponse
    {
        $user = auth()->id();
        $data = $request->validated();
        $data['create_user_id'] = auth()->id();

        try {
            DB::beginTransaction();
            $equipmentTutorial = new EquipmentMaintenanceTutorialStep();
            $equipmentTutorial->name = [
                'me' => $data['names'][0],
                'en' => $data['names'][1]
            ];
            $equipmentTutorial->description = [
                'me' => $data['descriptions'][0],
                'en' => $data['descriptions'][1]
            ];
            $equipmentTutorial->video_link = $data['video_link'];
            $equipmentTutorial->section_id = $data['section_id'];
            $equipmentTutorial->create_user_id = $user;
            $equipmentTutorial->save();

            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $newEquipmentPhoto = new EquipmentMaintenanceTutorialStepPhoto();
                    $newEquipmentPhoto->photo_path = self::storeFileFromBase64($photo, 'equipment-maintenance-tutorial-steps');
                    $newEquipmentPhoto->step_id = $equipmentTutorial->id;
                    $newEquipmentPhoto->create_user_id = $user;
                    $newEquipmentPhoto->save();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 500);
        }

        return response()->json(['success']);
    }

    public function update(EquipmentMaintenanceStepRequest $request, EquipmentMaintenanceTutorialStep $equipmentTutorial): JsonResponse
    {
        $user = auth()->id();
        $data = $request->validated();
        $data['update_user_id'] = auth()->id();

        try {
            DB::beginTransaction();
            $equipmentTutorial->name = [
                'me' => $data['names'][0],
                'en' => $data['names'][1]
            ];
            $equipmentTutorial->description = [
                'me' => $data['descriptions'][0],
                'en' => $data['descriptions'][1]
            ];
            $equipmentTutorial->video_link = $data['video_link'];
            $equipmentTutorial->section_id = $data['section_id'];
            $equipmentTutorial->update_user_id = $user;
            $equipmentTutorial->save();

            if (isset($data['delete_photos'])) {
                foreach ($data['delete_photos'] as $deletePhoto) {
                    $photo = EquipmentMaintenanceTutorialStepPhoto::findOrFail($deletePhoto);
                    $photo->delete();
                }
            }


            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $newEquipmentPhoto = new EquipmentMaintenanceTutorialStepPhoto();
                    $newEquipmentPhoto->photo_path = self::storeFileFromBase64($photo, 'equipment-maintenance-tutorial-steps');
                    $newEquipmentPhoto->step_id = $equipmentTutorial->id;
                    $newEquipmentPhoto->create_user_id = $user;
                    $newEquipmentPhoto->save();
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
        EquipmentMaintenanceTutorialStep::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function searchBeerPouringTutorial($keyword): JsonResponse
    {
        return response()->json(['success', EquipmentMaintenanceTutorialStep::searchEquipmentSteps($keyword)]);
    }

}
