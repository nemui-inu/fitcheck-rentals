import { router } from "@inertiajs/react";
import BookingController from "@/actions/App/Http/Controllers/Owner/BookingController";
import { Button } from "@/components/ui/button";
import type { Booking, BookingStatus } from "@/types";

const actions: Partial<
  Record<
    BookingStatus,
    { to: BookingStatus; label: string; primary?: boolean }[]
  >
> = {
  pending: [
    { to: "approved", label: "Approve", primary: true },
    { to: "rejected", label: "Reject" },
  ],
  approved: [
    { to: "active", label: "Mark picked up", primary: true },
    { to: "cancelled", label: "Cancel booking" },
  ],
  active: [{ to: "returned", label: "Mark returned", primary: true }],
};

export default function BookingActions({ booking }: { booking: Booking }) {
  return (
    <div className="flex gap-2">
      {(actions[booking.status] ?? []).map((action) => (
        <Button
          key={action.to}
          variant={action.primary ? "default" : "outline"}
          onClick={() =>
            router.patch(
              BookingController.update(booking.reference).url,
              { status: action.to },
              { preserveScroll: true },
            )
          }
        >
          {action.label}
        </Button>
      ))}
    </div>
  );
}
