<?php

namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;

class BeerFoodQARequest extends FormRequest
{
    protected $languagesCount;

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
        $this->languagesCount = Language::count();
        return $this->is('admin/beer-food-qa/store') ? $this->createRules() : $this->updateRules();
    }

    public function createRules(): array
    {
        return [
            'questions' => 'required|array|min:' . $this->languagesCount,
            'questions.*' => 'required|max:255',
            'answers' => 'required|array|min:' . $this->languagesCount,
            'answers.*' => 'required|max:10000',
            'category_id' => 'required'
        ];
    }

    public function updateRules(): array
    {
        return [
            'questions' => 'required|array|min:' . $this->languagesCount,
            'questions.*' => 'required|max:255',
            'answers' => 'required|array|min:' . $this->languagesCount,
            'answers.*' => 'required|max:10000',
            'category_id' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'questions.required' => 'Morate unijeti pitanje na svakom jeziku',
            'questions.*.required' => 'Morate unijeti pitanje na svakom jeziku',
            'answers.required' => 'Morate unijeti odgovor na svakom jeziku',
            'answers.*.required' => 'Morate unijeti odgovor na svakom jeziku',
            'category_id.required' => 'Morate odabrati kategoriju'
        ];
    }
}
