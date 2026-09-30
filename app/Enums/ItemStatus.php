<?php

namespace App\Enums;

enum ItemStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Paused = 'paused';
    case PendingReview = 'pending_review';
    case TakenDown = 'taken_down';

    /**
     * @return list<ItemStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active],
            self::Active => [self::Paused, self::TakenDown],
            self::Paused => [self::Active],
            self::TakenDown => [self::PendingReview],
            self::PendingReview => [self::Active, self::TakenDown],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }

    /**
     * Owners publish and pause their own items. Takedowns and reviews go through admins.
     */
    public function ownerCanTransitionTo(self $to): bool
    {
        return in_array($this, [self::Draft, self::Active, self::Paused], true)
            && in_array($to, self::ownerSettable(), true)
            && $this->canTransitionTo($to);
    }

    /**
     * @return list<ItemStatus>
     */
    public static function ownerSettable(): array
    {
        return [self::Active, self::Paused];
    }
}
