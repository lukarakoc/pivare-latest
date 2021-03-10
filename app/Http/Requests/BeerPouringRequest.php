<?php

namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;

class BeerPouringRequest extends FormRequest
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
    public function rules()
    {

        $this->languagesCount = Language::count();
        return $this->is('admin/beer_pouring_tutorials/store') ? $this->createRules() : $this->updateRules();
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
            'photos.*' => 'required'
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
            'photos' => 'required|array|min:1',
            'photos.*' => 'required',
            'delete_photos' => 'nullable'
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
            'photos.required' => 'Morate dodati najmanje 1 sliku',
            'video_link.required' => 'Morate unijeti video link'
        ];
    }
}
