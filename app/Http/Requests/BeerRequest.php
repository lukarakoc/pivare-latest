<?php

namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;

class BeerRequest extends FormRequest
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
        return $this->is('admin/beers/store') ? $this->createRules() : $this->updateRules();
    }

    public function createRules(): array
    {
        return [
            'names' => 'required|array|min:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|min:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required',
            'video_link' => 'required|url'
        ];
    }

    public function updateRules(): array
    {
        return [
            'names' => 'required|array|min:' . $this->languagesCount,
            'names.*' => 'required|max:255',
            'descriptions' => 'required|array|min:' . $this->languagesCount,
            'descriptions.*' => 'required|max:10000',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|max:10000',
            'video_link' => 'required|url'
        ];
    }

    public function messages(): array
    {
        return parent::messages();
    }
}
