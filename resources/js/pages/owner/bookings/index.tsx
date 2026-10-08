import { Head, Link } from "@inertiajs/react";
import BookingRow from "@/components/booking-row";
import EmptyState from "@/components/empty-state";
import Heading from "@/components/heading";
import InputError from "@/components/input-error";
import BookingActions from "@/components/owner/booking-actions";
import { cn } from "@/lib/utils";
import { index } from "@/routes/owner/bookings";
import type { Booking, BookingStatus } from "@/types";

const tabs: { label: string; status?: BookingStatus }[] = [
  { label: "All" },
  { label: "Pending", status: "pending" },
  { label: "Approved", status: "approved" },
  { label: "Out now", status: "active" },
  { label: "Returned", status: "returned" },
];

export default function OwnerBookings({
  bookings,
  status,
  errors,
}: {
  bookings: Booking[];
  status: BookingStatus | null;
  errors: Partial<Record<string, string>>;
}) {
  return (
    <>
      <Head title="Booking requests" />

      <div className="space-y-6 px-4 py-6">
        <Heading
          title="Booking requests"
          description="Approve requests, then mark pickups and returns."
        />

        <nav className="flex flex-wrap gap-2">
          {tabs.map((tab) => (
            <Link
              key={tab.label}
              href={index({
                query: tab.status ? { status: tab.status } : {},
              })}
              className={cn(
                "border px-3 py-1 text-sm",
                (status ?? undefined) === tab.status &&
                  "border-foreground bg-foreground text-background",
              )}
            >
              {tab.label}
            </Link>
          ))}
        </nav>

        <InputError message={errors.status} />

        {bookings.length === 0 ? (
          <EmptyState
            title="Nothing here"
            description="Bookings in this status will show up here."
          />
        ) : (
          <ul className="divide-y border">
            {bookings.map((booking) => (
              <li key={booking.id}>
                <BookingRow booking={booking}>
                  <BookingActions booking={booking} />
                </BookingRow>
              </li>
            ))}
          </ul>
        )}
      </div>
    </>
  );
}

OwnerBookings.layout = {
  breadcrumbs: [{ title: "Booking requests", href: index() }],
};
