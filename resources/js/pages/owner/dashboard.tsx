import { Head } from "@inertiajs/react";
import Heading from "@/components/heading";
import { dashboard } from "@/routes/owner";

type Profile = {
  shop_name: string;
  meetup_area: string;
};

export default function OwnerDashboard({ profile }: { profile: Profile }) {
  return (
    <>
      <Head title="Owner dashboard" />

      <div className="space-y-6 px-4 py-6">
        <Heading
          title={profile.shop_name}
          description={`Meetups in ${profile.meetup_area}`}
        />
      </div>
    </>
  );
}

OwnerDashboard.layout = {
  breadcrumbs: [{ title: "Owner dashboard", href: dashboard() }],
};
