import { Head, router } from "@inertiajs/react";
import BookingController from "@/actions/App/Http/Controllers/BookingController";
import InputError from "@/components/input-error";
import StatusBadge from "@/components/status-badge";
import { Button } from "@/components/ui/button";
import { formatRange } from "@/lib/dates";
import { formatPeso } from "@/lib/money";
import { index } from "@/routes/bookings";
import type { Booking } from "@/types";

export default function BookingShow({
  booking,
  daysLate,
  errors,
}: {
  booking: Booking;
  daysLate: number;
  errors: Partial<Record<string, string>>;
}) {
  const item = booking.unit.item;
  const owner = item.owner_profile;
  const canCancel =
    booking.status === "pending" || booking.status === "approved";

  const cancel = () => {
    if (confirm("Cancel this booking?")) {
      router.patch(BookingController.cancel(booking.reference).url);
    }
  };

  return (
    <>
      <Head title={`Booking ${booking.reference}`} />

      <div className="max-w-2xl space-y-6 px-4 py-6">
        <div className="space-y-2">
          <p className="font-mono text-xs text-muted-foreground">
            {booking.reference}
          </p>
          <h1>{item.name}</h1>
          <StatusBadge status={booking.status} />
        </div>

        {daysLate > 0 && (
          <p className="border border-destructive p-3 text-sm">
            <span className="mr-2 bg-foreground px-1.5 py-0.5 font-mono text-xs text-background">
              LATE
            </span>
            Returned {daysLate} {daysLate === 1 ? "day" : "days"} after the end
            date. No fee is charged.
          </p>
        )}

        <dl className="grid grid-cols-2 gap-4 border p-4 text-sm">
          <div>
            <dt className="text-muted-foreground">Dates</dt>
            <dd className="font-mono">
              {formatRange(booking.start_date, booking.end_date)}
            </dd>
          </div>
          <div>
            <dt className="text-muted-foreground">Unit</dt>
            <dd className="font-mono">{booking.unit.label}</dd>
          </div>
          <div>
            <dt className="text-muted-foreground">Total</dt>
            <dd className="font-mono">{formatPeso(booking.total)}</dd>
          </div>
          <div>
            <dt className="text-muted-foreground">Deposit</dt>
            <dd className="font-mono">{formatPeso(booking.deposit)}</dd>
          </div>
          {owner && (
            <div className="col-span-2">
              <dt className="text-muted-foreground">Meetup</dt>
              <dd>
                {owner.shop_name}, {owner.meetup_area}
                {booking.status !== "pending" && owner.user?.phone && (
                  <span className="font-mono"> · {owner.user.phone}</span>
                )}
              </dd>
            </div>
          )}
        </dl>

        <InputError message={errors.status} />

        {canCancel && (
          <Button
            variant="outline"
            onClick={cancel}
          >
            Cancel booking
          </Button>
        )}
      </div>
    </>
  );
}

BookingShow.layout = {
  breadcrumbs: [{ title: "My bookings", href: index() }],
};
