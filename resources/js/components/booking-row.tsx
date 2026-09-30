import type { ReactNode } from "react";
import StatusBadge from "@/components/status-badge";
import { formatRange } from "@/lib/dates";
import { formatPeso } from "@/lib/money";
import type { Booking } from "@/types";

export default function BookingRow({
  booking,
  children,
}: {
  booking: Booking;
  children?: ReactNode;
}) {
  return (
    <div className="flex flex-wrap items-center gap-4 p-3">
      <div className="min-w-0 flex-1 space-y-1">
        <p className="font-medium">
          {booking.unit.item.name}{" "}
          <span className="font-mono text-xs text-muted-foreground">
            {booking.unit.label}
          </span>
        </p>
        <p className="font-mono text-sm">
          {formatRange(booking.start_date, booking.end_date)} ·{" "}
          {formatPeso(booking.total)}
        </p>
        {booking.renter && (
          <p className="text-sm text-muted-foreground">
            {booking.renter.name}
            {booking.renter.phone && (
              <span className="font-mono"> · {booking.renter.phone}</span>
            )}
          </p>
        )}
      </div>
      <StatusBadge status={booking.status} />
      {children}
    </div>
  );
}
