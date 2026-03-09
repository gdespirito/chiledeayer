<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Por favor ingresa tu nombre.',
            'email.required' => 'Por favor ingresa tu correo electrónico.',
            'email.email' => 'Por favor ingresa un correo electrónico válido.',
            'subject.required' => 'Por favor ingresa un asunto.',
            'body.required' => 'Por favor ingresa tu mensaje.',
            'body.max' => 'El mensaje no puede superar los 5000 caracteres.',
        ];
    }
}
