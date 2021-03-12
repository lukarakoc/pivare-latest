<?php

namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;

class EquipmentMaintenanceSectionsRequest extends FormRequest
{
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
        return $this->is('admin/equipment-maintenance-sections/store') ? $this->createRules() : $this->updateRules();
    }

    public function createRules(): array
    {
        return [
            'names' => 'required|array|max:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|max:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',

        ];
    }

    public function updateRules(): array
    {
        return [
            'names' => 'required|array|max:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|max:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',
        ];
    }

    public function messages()
    {
        return [
            'names.required' => 'Morate unijeti naziv na svakom jeziku',
            'names.*.required' => 'Morate unijeti naziv na svakom jeziku',
            'descriptions.required' => 'Morate unijeti opis na svakom jeziku',
            'descriptions.*.required' => 'Morate unijeti opis na svakom jeziku',
        ];
    }
}
