<?php

namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;

class BeerFoodArticleRequest extends FormRequest
{

    protected $languageCount;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
        $this->languageCount = Language::count();
        return $this->is('admin/beer-food-articles/store') ? $this->createRules() : $this->updateRules();
    }

    private function createRules(): array
    {
        return [
            'titles' => 'required|array|max:' . $this->languageCount,
            'titles.*' => 'required|max:255',
            'texts' => 'required|array|max:' . $this->languageCount,
            'texts.*' => 'required|max:10000',
            'category_id' => 'required',
            'video_link' => 'required|url',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required'
        ];
    }

    private function updateRules(): array
    {
        return [
            'titles' => 'required|array|max:' . $this->languageCount,
            'titles.*' => 'required|max:255',
            'texts' => 'required|array|max:' . $this->languageCount,
            'texts.*' => 'required|max:10000',
            'category_id' => 'required',
            'video_link' => 'required|url',
            'photos' => 'nullable|array|min:1',
            'photos.*' => 'nullable',
            'delete_photos' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'titles.required' => 'Morate unijeti naziv na svakom jeziku',
            'titles.*.required' => 'Morate unijeti naziv na svakom jeziku',
            'titles.*.max' => 'Naziv može sadržati najviše 255 karaktera',
            'texts.required' => 'Morate unijeti opis na svakom jeziku',
            'texts.*.required' => 'Morate unijeti opis na svakom jeziku',
            'texts.*.max' => 'Opis može sadržati najviše 10000 karaktera',
            'video_link.required' => 'Morate unijeti video link',
            'category_id.required' => 'Morate odabrati kategoriju',
            'video_link.url' => 'Video link nije u validnom formatu',
            'photos.required' => 'Morate dodati slike',
            'photos.*.required' => 'Morate dodati slike',
        ];
    }
}
