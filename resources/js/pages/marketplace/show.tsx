import { Head } from "@inertiajs/react";
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

          {isOwnItem && (
            <p className="text-sm text-muted-foreground">
              This is your item. Owners cannot book their own items.
            </p>
          )}
        </div>
      </div>
    </>
  );
}

MarketplaceShow.layout = {
  breadcrumbs: [{ title: "Marketplace", href: index() }],
};
