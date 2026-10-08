import { Form, Head, Link, usePage } from "@inertiajs/react";
import BookingController from "@/actions/App/Http/Controllers/BookingController";
import InputError from "@/components/input-error";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { login } from "@/routes";
import { useState } from "react";
import { formatPeso } from "@/lib/money";
import { cn } from "@/lib/utils";
import { index } from "@/routes/marketplace";
import type { Item, OwnerProfile } from "@/types";

export type MarketplaceItem = Item & {
  owner_profile: OwnerProfile;
  available_units_count: number;
};

export default function MarketplaceShow({
  item,
  isOwnItem,
}: {
  item: MarketplaceItem;
  isOwnItem: boolean;
}) {
  const { auth } = usePage().props;
  const images = item.images ?? [];
  const [selected, setSelected] = useState(0);

  return (
    <>
      <Head title={item.name} />

      <div className="grid gap-8 px-4 py-6 lg:grid-cols-2">
        <div className="space-y-3">
          <div className="aspect-[3/4] border bg-muted">
            {images[selected] && (
              <img
                src={images[selected].url}
                alt={item.name}
                className="size-full object-cover"
              />
            )}
          </div>
          {images.length > 1 && (
            <div className="flex gap-2">
              {images.map((image, position) => (
                <button
                  key={image.id}
                  type="button"
                  onClick={() => setSelected(position)}
                  className={cn(
                    "size-16 border bg-muted",
                    position === selected &&
                      "outline-[1.5px] outline-offset-3 outline-primary outline-solid",
                  )}
                  aria-label={`Show photo ${position + 1}`}
                >
                  <img
                    src={image.url}
                    alt=""
                    className="size-full object-cover"
                  />
                </button>
              ))}
            </div>
          )}
        </div>

        <div className="space-y-6">
          <div className="space-y-2">
            <p className="font-mono text-xs text-muted-foreground uppercase">
              {item.category?.name}
            </p>
            <h1>{item.name}</h1>
            {(item.series || item.character) && (
              <p className="text-muted-foreground">
                {[item.character, item.series].filter(Boolean).join(" from ")}
              </p>
            )}
          </div>

          <dl className="grid grid-cols-2 gap-4 border p-4 text-sm">
            <div>
              <dt className="text-muted-foreground">Daily rate</dt>
              <dd className="font-mono text-lg">
                {formatPeso(item.daily_rate)}
              </dd>
            </div>
            <div>
              <dt className="text-muted-foreground">Deposit</dt>
              <dd className="font-mono text-lg">{formatPeso(item.deposit)}</dd>
            </div>
            <div>
              <dt className="text-muted-foreground">Size</dt>
              <dd>{item.size ?? "Not listed"}</dd>
            </div>
            <div>
              <dt className="text-muted-foreground">Units</dt>
              <dd className="font-mono">{item.available_units_count}</dd>
            </div>
            <div className="col-span-2">
              <dt className="text-muted-foreground">Shop</dt>
              <dd>
                {item.owner_profile.shop_name}, meetups in{" "}
                {item.owner_profile.meetup_area}
              </dd>
            </div>
          </dl>

          {item.description && (
            <p className="whitespace-pre-line">{item.description}</p>
          )}

          {isOwnItem ? (
            <p className="text-sm text-muted-foreground">
              This is your item. Owners cannot book their own items.
            </p>
          ) : !auth.user ? (
            <Button asChild>
              <Link href={login()}>Log in to book</Link>
            </Button>
          ) : (
            <Form
              {...BookingController.store.form(item.id)}
              className="space-y-4 border p-4"
            >
              {({ processing, errors }) => (
                <>
                  <h3>Request these dates</h3>
                  <div className="grid grid-cols-2 gap-3">
                    <div className="grid gap-2">
                      <Label htmlFor="start_date">Start date</Label>
                      <Input
                        id="start_date"
                        name="start_date"
                        type="date"
                        required
                        className="font-mono"
                      />
                    </div>
                    <div className="grid gap-2">
                      <Label htmlFor="end_date">End date</Label>
                      <Input
                        id="end_date"
                        name="end_date"
                        type="date"
                        required
                        className="font-mono"
                      />
                    </div>
                  </div>
                  {!auth.user.phone && (
                    <div className="grid gap-2">
                      <Label htmlFor="phone">Phone number</Label>
                      <Input
                        id="phone"
                        name="phone"
                        type="tel"
                        required
                        placeholder="09171234567"
                        className="font-mono"
                      />
                      <p className="text-sm text-muted-foreground">
                        The owner uses this to arrange the meetup.
                      </p>
                      <InputError message={errors.phone} />
                    </div>
                  )}
                  <InputError message={errors.start_date ?? errors.end_date} />
                  <Button
                    disabled={processing}
                    className="outline-[1.5px] outline-offset-3 outline-primary outline-solid"
                  >
                    Send booking request
                  </Button>
                </>
              )}
            </Form>
          )}
        </div>
      </div>
    </>
  );
}

MarketplaceShow.layout = {
  breadcrumbs: [{ title: "Marketplace", href: index() }],
};
