import { Head, Link } from "@inertiajs/react";
import BookingRow from "@/components/booking-row";
import EmptyState from "@/components/empty-state";
import Heading from "@/components/heading";
import { Button } from "@/components/ui/button";
import { index, show } from "@/routes/bookings";
import { index as marketplace } from "@/routes/marketplace";
import type { Booking } from "@/types";

export default function BookingsIndex({ bookings }: { bookings: Booking[] }) {
  return (
    <>
      <Head title="My bookings" />

      <div className="space-y-6 px-4 py-6">
        <Heading
          title="My bookings"
          description="Everything you have requested or rented."
        />

        {bookings.length === 0 ? (
          <EmptyState
            title="No bookings yet"
            description="Find a fit in the marketplace and request your dates."
            action={
              <Button asChild>
                <Link href={marketplace()}>Browse the marketplace</Link>
              </Button>
            }
          />
        ) : (
          <ul className="divide-y border">
            {bookings.map((booking) => (
              <li key={booking.id}>
                <Link
                  href={show(booking.reference)}
                  className="block hover:bg-muted"
                >
                  <BookingRow booking={booking} />
                </Link>
              </li>
            ))}
          </ul>
        )}
      </div>
    </>
  );
}

BookingsIndex.layout = {
  breadcrumbs: [{ title: "My bookings", href: index() }],
};
