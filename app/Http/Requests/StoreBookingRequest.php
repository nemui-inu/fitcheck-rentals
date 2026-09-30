<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'phone' => [$this->user()->phone === null ? 'required' : 'nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'Pick a start date from today onward.',
            'end_date.after_or_equal' => 'Pick an end date on or after the start date.',
            'phone.required' => 'Add a phone number so the owner can reach you for the meetup.',
            'phone.regex' => 'Use digits only, like 09171234567.',
        ];
    }
}
