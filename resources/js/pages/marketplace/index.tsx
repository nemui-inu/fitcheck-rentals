import { Head, Link, router } from "@inertiajs/react";
import type { FormEvent } from "react";
import EmptyState from "@/components/empty-state";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { formatPeso } from "@/lib/money";
import { index, show } from "@/routes/marketplace";
import type { Category, Item, Paginated } from "@/types";

type Filters = {
  search?: string;
  category?: string;
  size?: string;
  min_price?: string;
  max_price?: string;
};

const selectClass = "h-9 border border-input bg-transparent px-3 text-sm";

export default function Marketplace({
  items,
  filters,
  categories,
  sizes,
}: {
  items: Paginated<Item>;
  filters: Filters;
  categories: Category[];
  sizes: string[];
}) {
  const applyFilters = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const query = Object.fromEntries(
      [...new FormData(event.currentTarget).entries()].filter(
        ([, value]) => value !== "",
      ),
    );
    router.get(index().url, query, { preserveState: true });
  };

  return (
    <>
      <Head title="Marketplace" />

      <div className="space-y-4 px-4 py-6">
        <header>
          <h1>
            Rent the fit<span className="text-primary">.</span>
          </h1>
          <p className="text-muted-foreground">
            Costumes, wigs, and props from local owners. Meet up, suit up.
          </p>
        </header>

        <form
          onSubmit={applyFilters}
          className="flex gap-2"
        >
          <Input
            name="search"
            defaultValue={filters.search}
            placeholder="Search name, series, or character"
            className="md:col-span-2"
          />
          <select
            name="category"
            defaultValue={filters.category ?? ""}
            className={selectClass}
            aria-label="Category"
          >
            <option value="">All categories</option>
            {categories.map((category) => (
              <option
                key={category.id}
                value={category.code}
              >
                {category.name}
              </option>
            ))}
          </select>
          <select
            name="size"
            defaultValue={filters.size ?? ""}
            className={selectClass}
            aria-label="Size"
          >
            <option value="">Any size</option>
            {sizes.map((size) => (
              <option key={size}>{size}</option>
            ))}
          </select>
          <div className="flex gap-2">
            <Input
              name="min_price"
              type="number"
              min="0"
              defaultValue={filters.min_price}
              placeholder="Min ₱"
              className="font-mono"
            />
            <Input
              name="max_price"
              type="number"
              min="0"
              defaultValue={filters.max_price}
              placeholder="Max ₱"
              className="font-mono"
            />
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              asChild
            >
              <Link href={index()}>Clear</Link>
            </Button>
            <Button className="flex-1">Apply filters</Button>
          </div>
        </form>

        {items.data.length === 0 ? (
          <EmptyState
            title="No fits found"
            description="Nothing matches those filters. Clear them or try another search."
          />
        ) : (
          <ul className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            {items.data.map((item) => (
              <li key={item.id}>
                <Link
                  href={show(item.id)}
                  className="group block border bg-card"
                >
                  <div className="aspect-[3/4] bg-muted">
                    {item.cover_image && (
                      <img
                        src={item.cover_image.url}
                        alt={item.name}
                        className="size-full object-cover"
                      />
                    )}
                  </div>
                  <div className="space-y-1 p-3">
                    <p className="truncate font-medium group-hover:underline">
                      {item.name}
                    </p>
                    <p className="truncate text-sm text-muted-foreground">
                      {[item.series, item.character]
                        .filter(Boolean)
                        .join(", ") || item.category?.name}
                    </p>
                    <p className="font-mono text-sm">
                      {formatPeso(item.daily_rate)}/day
                    </p>
                  </div>
                </Link>
              </li>
            ))}
          </ul>
        )}

        {items.last_page > 1 && (
          <nav className="flex items-center justify-between">
            <Button
              variant="outline"
              disabled={!items.prev_page_url}
              asChild={!!items.prev_page_url}
            >
              {items.prev_page_url ? (
                <Link
                  href={items.prev_page_url}
                  preserveState
                >
                  Previous page
                </Link>
              ) : (
                "Previous page"
              )}
            </Button>
            <span className="font-mono text-sm">
              {items.current_page} / {items.last_page}
            </span>
            <Button
              variant="outline"
              disabled={!items.next_page_url}
              asChild={!!items.next_page_url}
            >
              {items.next_page_url ? (
                <Link
                  href={items.next_page_url}
                  preserveState
                >
                  Next page
                </Link>
              ) : (
                "Next page"
              )}
            </Button>
          </nav>
        )}
      </div>
    </>
  );
}

Marketplace.layout = {
  breadcrumbs: [{ title: "Marketplace", href: index() }],
};
