<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class BatchUploadRequest extends FormRequest
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
            'photos' => ['required', 'array', 'min:1', 'max:50'],
            'photos.*' => ['required', File::image()->max(20 * 1024)],
            'shared_description' => ['nullable', 'string', 'max:2000'],
            'shared_year_from' => ['required', 'integer', 'min:1800', 'max:'.date('Y')],
            'shared_year_to' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y'), 'gte:shared_year_from'],
            'shared_date_precision' => ['required', 'string', 'in:exact,year,decade,circa'],
            'shared_place_id' => ['nullable', 'exists:places,id'],
            'shared_source_credit' => ['nullable', 'string', 'max:500'],
            'shared_tags' => ['nullable', 'array'],
            'shared_tags.*' => ['string', 'max:100'],
            'overrides' => ['nullable', 'array'],
            'overrides.*.description' => ['nullable', 'string', 'max:2000'],
            'overrides.*.year_from' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'overrides.*.year_to' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'overrides.*.place_id' => ['nullable', 'exists:places,id'],
        ];
    }
}
