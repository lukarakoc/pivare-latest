<?php

namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
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
        return $this->is('admin/locations/store') ? $this->createRules() : $this->updateRules();
    }

    public function createRules(): array
    {
        return [
            'names' => 'required|array|min:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'addresses' => 'required|array|min:' . $this->languagesCount,
            'addresses.*' => 'required|max:255',
            'descriptions' => 'required|array|min:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',
            'category_id' => 'required',
            'logo' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required',
        ];
    }

    public function updateRules(): array
    {
        return [
            'names' => 'required|array|min:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'addresses' => 'required|array|min:' . $this->languagesCount,
            'addresses.*' => 'required|max:255',
            'descriptions' => 'required|array|min:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',
            'category_id' => 'required',
            'logo' => 'nullable',
            'latitude' => 'required',
            'longitude' => 'required',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable',
            'delete_photos' => 'nullable'
        ];
    }

    public function messages(): array
    {
        return [
            'names.required' => 'Morate unijeti naziv na svakom jeziku',
            'names.*.required' => 'Morate unijeti naziv na svakom jeziku',
            'names.*.max' => 'Naziv može sadržati najviše 255 karaktera',
            'addresses.required' => 'Morate unijeti adresu na svakom jeziku',
            'addresses.*.required' => 'Morate unijeti adresu na svakom jeziku',
            'addresses.*.max' => 'Adresa može sadržati najviše 255 karaktera',
            'descriptions.required' => 'Morate unijeti opis na svakom jeziku',
            'descriptions.*.required' => 'Morate unijeti opis na svakom jeziku',
            'descriptions.*.max' => 'Opis može sadržati najviše 10000 karaktera',
            'category_id.required' => 'Morate odabrati kategoriju',
            'logo.required' => 'Morate dodati logotip',
            'latitude.required' => 'Morate označiti lokaciju na mapi',
            'longitude.required' => 'Morate označiti lokaciju na mapi',
            'photos.required' => 'Morate dodati slike za lokaciju',
            'photos.*.required' => 'Morate dodati slike za lokaciju',
        ];
    }
}
