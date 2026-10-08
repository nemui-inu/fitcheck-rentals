import { Form, Head } from "@inertiajs/react";
import OwnerProfileController from "@/actions/App/Http/Controllers/Owner/OwnerProfileController";
import Heading from "@/components/heading";
import InputError from "@/components/input-error";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { setup } from "@/routes/owner";

export default function OwnerSetup({ hasPhone }: { hasPhone: boolean }) {
  return (
    <>
      <Head title="Open your shop" />

      <div className="max-w-xl px-4 py-6">
        <Heading
          title="Open your shop"
          description="Renters see your shop name and meet you in your meetup area."
        />

        <Form
          {...OwnerProfileController.store.form()}
          className="space-y-6"
        >
          {({ processing, errors }) => (
            <>
              <div className="grid gap-2">
                <Label htmlFor="shop_name">Shop name</Label>
                <Input
                  id="shop_name"
                  name="shop_name"
                  required
                  placeholder="Wig Closet"
                />
                <InputError message={errors.shop_name} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="meetup_area">Meetup area</Label>
                <Input
                  id="meetup_area"
                  name="meetup_area"
                  required
                  placeholder="Cubao, Quezon City"
                />
                <InputError message={errors.meetup_area} />
              </div>

              {!hasPhone && (
                <div className="grid gap-2">
                  <Label htmlFor="phone">Phone number</Label>
                  <Input
                    id="phone"
                    name="phone"
                    type="tel"
                    required
                    autoComplete="tel"
                    placeholder="09171234567"
                    className="font-mono"
                  />
                  <InputError message={errors.phone} />
                </div>
              )}

              <Button disabled={processing}>Open my shop</Button>
            </>
          )}
        </Form>
      </div>
    </>
  );
}

OwnerSetup.layout = {
  breadcrumbs: [{ title: "Open your shop", href: setup() }],
};
