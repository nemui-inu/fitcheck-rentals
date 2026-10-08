<?php

namespace App\Actions;

use App\Enums\ItemStatus;
use App\Models\Item;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TakeDownItem
{
    /**
     * Pull an active or resubmitted item from the marketplace with a reason.
     *
     * @throws ValidationException
     */
    public function __invoke(Item $item, User $admin, string $reason): void
    {
        if (! $item->status->canTransitionTo(ItemStatus::TakenDown)) {
            throw ValidationException::withMessages(['reason' => __('Only active or resubmitted items can be taken down.')]);
        }

        $item->status = ItemStatus::TakenDown;
        $item->taken_down_at = now();
        $item->taken_down_by = $admin->id;
        $item->takedown_reason = $reason;
        $item->save();
    }
}
