<?php

namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;

class LocationDiscountRequest extends FormRequest
{
    protected $languagesCount;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->languagesCount = Language::count();
        return $this->is('admin/locations-discounts/store') ? $this->createRules() : $this->updateRules();
    }

    public function createRules(): array
    {
        return [
            'names' => 'required|array|min:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|min:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',
            'location' => 'required',
            'amount' => 'required',
            'date_from' => 'required',
            'date_to' => 'required'
        ];
    }

    public function updateRules(): array
    {
        return [
            'names' => 'required|array|min:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|min:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',
            'location' => 'required',
            'amount' => 'required',
            'date_from' => 'required',
            'date_to' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'names.required' => 'Morate unijeti naziv na svakom jeziku',
            'names.*.required' => 'Morate unijeti naziv na svakom jeziku',
            'names.*.max' => 'Naziv može sadržati najviše 255 karaktera',
            'descriptions.required' => 'Morate unijeti opis na svakom jeziku',
            'descriptions.*.required' => 'Morate unijeti opis na svakom jeziku',
            'descriptions.*.max' => 'Opis može sadržati najviše 10000 karaktera',
            'location.required' => 'Morate odabrati kategoriju',
            'amount.required' => 'Morate unijeti iznos popusta',
            'date_from.required' => 'Morate odabrati datum od',
            'date_to.required' => 'Morate odabrati datum do'
        ];
    }
}
