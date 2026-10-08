import { Head, Link, usePage } from "@inertiajs/react";
import Heading from "@/components/heading";
import { Button } from "@/components/ui/button";
import { dashboard } from "@/routes";
import { index as bookings } from "@/routes/bookings";
import { index as marketplace } from "@/routes/marketplace";
import {
  dashboard as ownerDashboard,
  setup as ownerSetup,
} from "@/routes/owner";

export default function Dashboard() {
  const { auth, isOwner } = usePage().props;

  const cards = [
    {
      title: "Find a fit",
      body: "Browse active listings and request your dates.",
      href: marketplace(),
      action: "Browse the marketplace",
    },
    {
      title: "My bookings",
      body: "Track requests, pickups, and returns.",
      href: bookings(),
      action: "View my bookings",
    },
    isOwner
      ? {
          title: "Your shop",
          body: "Answer requests and manage your items.",
          href: ownerDashboard(),
          action: "Open shop dashboard",
        }
      : {
          title: "Rent out your costumes",
          body: "Open a shop to list items and earn from your closet.",
          href: ownerSetup(),
          action: "Open a shop",
        },
  ];

  return (
    <>
      <Head title="Dashboard" />

      <div className="space-y-6 px-4 py-6">
        <Heading
          title={`Hi, ${auth.user.name}`}
          description="What are we suiting up for?"
        />

        <div className="grid gap-4 md:grid-cols-3">
          {cards.map((card) => (
            <div
              key={card.title}
              className="flex flex-col gap-3 border p-5"
            >
              <h3>{card.title}</h3>
              <p className="flex-1 text-sm text-muted-foreground">
                {card.body}
              </p>
              <Button
                variant="outline"
                asChild
              >
                <Link href={card.href}>{card.action}</Link>
              </Button>
            </div>
          ))}
        </div>
      </div>
    </>
  );
}

Dashboard.layout = {
  breadcrumbs: [
    {
      title: "Dashboard",
      href: dashboard(),
    },
  ],
};
