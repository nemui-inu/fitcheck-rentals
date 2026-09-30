import { Head, Link } from "@inertiajs/react";
import BookingRow from "@/components/booking-row";
import EmptyState from "@/components/empty-state";
import Heading from "@/components/heading";
import InputError from "@/components/input-error";
import BookingActions from "@/components/owner/booking-actions";
import { dashboard } from "@/routes/owner";
import { index as bookingsIndex } from "@/routes/owner/bookings";
import type { Booking, OwnerProfile } from "@/types";

export default function OwnerDashboard({
  profile,
  pendingBookings,
  counts,
  errors,
}: {
  profile: OwnerProfile;
  pendingBookings: Booking[];
  counts: { items: number; upcoming: number; out: number };
  errors: Partial<Record<string, string>>;
}) {
  const stats = [
    { label: "Items", value: counts.items },
    { label: "Pending", value: pendingBookings.length },
    { label: "Upcoming", value: counts.upcoming },
    { label: "Out now", value: counts.out },
  ];

  return (
    <>
      <Head title="Owner dashboard" />

      <div className="space-y-6 px-4 py-6">
        <Heading
          title={profile.shop_name}
          description={`Meetups in ${profile.meetup_area}`}
        />

        <dl className="grid grid-cols-2 gap-3 md:grid-cols-4">
          {stats.map((stat) => (
            <div
              key={stat.label}
              className="border p-4"
            >
              <dt className="text-sm text-muted-foreground">{stat.label}</dt>
              <dd className="font-mono text-2xl">{stat.value}</dd>
            </div>
          ))}
        </dl>

        <section className="space-y-3">
          <div className="flex items-center justify-between">
            <h3>Pending requests</h3>
            <Link
              href={bookingsIndex()}
              className="text-sm underline"
            >
              See all bookings
            </Link>
          </div>
          <InputError message={errors.status} />
          {pendingBookings.length === 0 ? (
            <EmptyState
              title="No pending requests"
              description="New booking requests show up here."
            />
          ) : (
            <ul className="divide-y border">
              {pendingBookings.map((booking) => (
                <li key={booking.id}>
                  <BookingRow booking={booking}>
                    <BookingActions booking={booking} />
                  </BookingRow>
                </li>
              ))}
            </ul>
          )}
        </section>
      </div>
    </>
  );
}

OwnerDashboard.layout = {
  breadcrumbs: [{ title: "Owner dashboard", href: dashboard() }],
};
