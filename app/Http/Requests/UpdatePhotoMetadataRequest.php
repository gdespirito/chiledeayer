<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoMetadataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:2000'],
            'year_from' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'year_to' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y'), 'gte:year_from'],
            'date_precision' => ['nullable', 'string', 'in:exact,year,decade,circa'],
            'place_id' => ['nullable', 'exists:places,id'],
            'source_credit' => ['nullable', 'string', 'max:500'],
            'heading' => ['nullable', 'numeric', 'min:0', 'max:360'],
            'pitch' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
        ];
    }
}
