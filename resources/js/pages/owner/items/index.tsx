import { Head, Link } from "@inertiajs/react";
import ItemController from "@/actions/App/Http/Controllers/Owner/ItemController";
import EmptyState from "@/components/empty-state";
import Heading from "@/components/heading";
import StatusBadge from "@/components/status-badge";
import { Button } from "@/components/ui/button";
import { formatPeso } from "@/lib/money";
import { index } from "@/routes/owner/items";
import type { Item } from "@/types";

export default function ItemsIndex({ items }: { items: Item[] }) {
  return (
    <>
      <Head title="My items" />

      <div className="px-4 py-6">
        <div className="flex items-start justify-between gap-4">
          <Heading
            title="My items"
            description="Costumes, wigs, and props you rent out."
          />
          <Button asChild>
            <Link href={ItemController.create()}>Add an item</Link>
          </Button>
        </div>

        {items.length === 0 ? (
          <EmptyState
            title="Nothing listed yet"
            description="Add your first item, then give it photos and units."
          />
        ) : (
          <ul className="divide-y border">
            {items.map((item) => (
              <li key={item.id}>
                <Link
                  href={ItemController.edit(item.id)}
                  className="flex items-center gap-4 p-3 hover:bg-muted"
                >
                  <div className="size-14 shrink-0 bg-muted">
                    {item.cover_image && (
                      <img
                        src={item.cover_image.url}
                        alt=""
                        className="size-full object-cover"
                      />
                    )}
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2">
                      <StatusBadge status={item.status} />
                      <p className="truncate font-medium">{item.name}</p>
                    </div>
                    <p className="text-sm text-muted-foreground">
                      {item.category?.name} · {item.units_count} units
                    </p>
                  </div>
                  <span className="font-mono text-sm">
                    {formatPeso(item.daily_rate)}/day
                  </span>
                </Link>
              </li>
            ))}
          </ul>
        )}
      </div>
    </>
  );
}

ItemsIndex.layout = {
  breadcrumbs: [{ title: "My items", href: index() }],
};
