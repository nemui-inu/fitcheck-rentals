<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Active = 'active';
    case Returned = 'returned';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * Allowed transitions, keyed by target status, listing who may make them.
     *
     * @return array<string, list<string>>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [
                self::Approved->value => ['owner'],
                self::Rejected->value => ['owner'],
                self::Cancelled->value => ['renter'],
            ],
            self::Approved => [
                self::Active->value => ['owner'],
                self::Cancelled->value => ['renter', 'owner'],
            ],
            self::Active => [
                self::Returned->value => ['owner'],
            ],
            self::Returned, self::Rejected, self::Cancelled => [],
        };
    }

    /**
     * @param  'owner'|'renter'  $actor
     */
    public function canTransitionTo(self $to, string $actor): bool
    {
        return in_array($actor, $this->allowedTransitions()[$to->value] ?? [], true);
    }

    /**
     * Statuses that hold a unit for their date range.
     *
     * @return list<BookingStatus>
     */
    public static function blocking(): array
    {
        return [self::Approved, self::Active];
    }
}
