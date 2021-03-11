<?php

namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;

class EquipmentMaintenanceStepRequest extends FormRequest
{
    protected $languagesCount;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->languagesCount = Language::count();
        return $this->is('admin/equipment-maintenance-step/store') ? $this->createRules() : $this->updateRules();
    }

    public function createRules(): array
    {
        return [
            'names' => 'required|array|max:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|max:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',
            'video_link' => 'required|url',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required',
            'section_id' => 'required'
        ];
    }

    public function updateRules(): array
    {
        return [
            'names' => 'required|array|max:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|max:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',
            'video_link' => 'required|url',
            'photos' => 'nullable|array|min:1',
            'photos.*' => 'nullable',
            'delete_photos' => 'nullable'
        ];
    }

}
