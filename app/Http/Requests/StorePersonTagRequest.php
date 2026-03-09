<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePersonTagRequest extends FormRequest
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
            'person_name' => ['required_without:person_id', 'nullable', 'string', 'max:255'],
            'person_id' => ['nullable', 'exists:people,id'],
            'x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'label' => ['nullable', 'string', 'max:255'],
        ];
    }
}
