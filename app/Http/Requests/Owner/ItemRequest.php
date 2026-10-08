<?php

namespace App\Http\Requests\Owner;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentCategoryId = $this->route('item') !== null
            ? $this->user()->ownerProfile->items()->whereKey($this->route('item'))->value('category_id')
            : null;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists(Category::class, 'id')->where(
                    fn ($query) => $query->where('is_active', true)->orWhere('id', $currentCategoryId)
                ),
            ],
            'name' => ['required', 'string', 'max:120'],
            'series' => ['nullable', 'string', 'max:120'],
            'character' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:2000'],
            'daily_rate' => ['required', 'numeric', 'min:1', 'max:100000'],
            'deposit' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'Pick an active category.',
        ];
    }

    /**
     * Money fields converted from pesos to centavos.
     *
     * @return array{daily_rate: int, deposit: int}
     */
    public function money(): array
    {
        return [
            'daily_rate' => (int) round($this->float('daily_rate') * 100),
            'deposit' => (int) round($this->float('deposit') * 100),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->safe()->except(['daily_rate', 'deposit']);
    }
}
