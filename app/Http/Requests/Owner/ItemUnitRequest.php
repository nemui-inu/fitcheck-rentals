<?php

namespace App\Http\Requests\Owner;

use App\Enums\UnitCondition;
use App\Enums\UnitStatus;
use App\Models\ItemUnit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemUnitRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['label' => strtoupper(trim((string) $this->input('label')))]);
    }

    /**
     * Labels are unique across all of this owner's units.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $ownerItemIds = $this->user()->ownerProfile->items()->pluck('id')->all();

        return [
            'label' => [
                'required',
                'string',
                'max:20',
                Rule::unique(ItemUnit::class, 'label')
                    ->whereIn('item_id', $ownerItemIds)
                    ->ignore($this->route('unit')),
            ],
            'condition' => ['required', Rule::enum(UnitCondition::class)],
            'status' => ['sometimes', Rule::enum(UnitStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'label.unique' => 'You already use this label. Try the suggested one.',
        ];
    }
}
