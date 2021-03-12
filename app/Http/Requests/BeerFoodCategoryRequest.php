<?php


namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;

class BeerFoodCategoryRequest extends FormRequest
{

    protected $languagesCount;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $this->languagesCount = Language::count();
        return $this->is('admin/beer-food-category/store') ? $this->createRules() : $this->updateRules();
    }

    private function createRules(): array
    {
        return [
            'names' => 'required|array|min:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|min:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000'
        ];
    }

    public function updateRules(): array
    {
        return [
            'names' => 'required|array|min:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|min:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000'
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
        ];
    }
}
