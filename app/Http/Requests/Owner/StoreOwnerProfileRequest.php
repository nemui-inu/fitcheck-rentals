<?php

namespace App\Http\Requests\Owner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOwnerProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'max:100'],
            'meetup_area' => ['required', 'string', 'max:150'],
            'phone' => [$this->user()->phone === null ? 'required' : 'nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Add a phone number so renters can reach you at meetups.',
            'phone.regex' => 'Use digits only, like 09171234567.',
        ];
    }
}
