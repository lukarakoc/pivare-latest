<?php

namespace App\Http\Controllers;

use App\EquipmentMaintenanceTutorialSection;
use App\Http\Requests\EquipmentMaintenanceSectionsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EquipmentMaintenanceSectionsController extends Controller
{


    public function store(EquipmentMaintenanceSectionsRequest $request): JsonResponse
    {
        $user = auth()->id();
        $data = $request->validated();
        $data['create_user_id'] = auth()->id();

        try {
            DB::beginTransaction();
            $equipmentTutorial = new EquipmentMaintenanceTutorialSection();
            $equipmentTutorial->name = [
                'me' => $data['names'][0],
                'en' => $data['names'][1]
            ];
            $equipmentTutorial->description = [
                'me' => $data['descriptions'][0],
                'en' => $data['descriptions'][1]
            ];

            $equipmentTutorial->create_user_id = $user;
            $equipmentTutorial->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 500);
        }

        return response()->json(['success']);
    }

    public function update(EquipmentMaintenanceSectionsRequest $request, EquipmentMaintenanceTutorialSection $equipmentTutorial): JsonResponse
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

            $equipmentTutorial->update_user_id = $user;
            $equipmentTutorial->save();


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], 500);
        }

        return response()->json(['success']);
    }

    public function destroy($id): JsonResponse
    {
        EquipmentMaintenanceTutorialSection::findOrFail($id)->delete();
        return response()->json(['success']);
    }

    public function searchEquipmentMaintenanceSections($keyword): JsonResponse
    {
        return response()->json(['success', EquipmentMaintenanceTutorialSection::searchEquipmentSections($keyword)]);
    }

}
