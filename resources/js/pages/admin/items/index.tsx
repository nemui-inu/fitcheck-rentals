import { Form, Head, Link, router } from "@inertiajs/react";
import ItemReviewController from "@/actions/App/Http/Controllers/Admin/ItemReviewController";
import EmptyState from "@/components/empty-state";
import Heading from "@/components/heading";
import InputError from "@/components/input-error";
import StatusBadge from "@/components/status-badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { formatPeso } from "@/lib/money";
import { cn } from "@/lib/utils";
import { index } from "@/routes/admin/items";
import { show } from "@/routes/marketplace";
import type { Item, ItemStatus, OwnerProfile, Paginated } from "@/types";

const tabs: { label: string; status: ItemStatus }[] = [
  { label: "Needs review", status: "pending_review" },
  { label: "Live", status: "active" },
  { label: "Taken down", status: "taken_down" },
];

type ReviewItem = Item & { owner_profile: OwnerProfile };

export default function AdminItems({
  items,
  status,
}: {
  items: Paginated<ReviewItem>;
  status: ItemStatus;
}) {
  return (
    <>
      <Head title="Item moderation" />

      <div className="space-y-6 px-4 py-6">
        <Heading
          title="Item moderation"
          description="Review resubmissions and take down items that break the rules."
        />

        <nav className="flex gap-2">
          {tabs.map((tab) => (
            <Link
              key={tab.status}
              href={index({ query: { status: tab.status } })}
              className={cn(
                "border px-3 py-1 text-sm",
                status === tab.status &&
                  "border-foreground bg-foreground text-background",
              )}
            >
              {tab.label}
            </Link>
          ))}
        </nav>

        {items.data.length === 0 ? (
          <EmptyState
            title="All clear"
            description="No items in this list right now."
          />
        ) : (
          <ul className="divide-y border">
            {items.data.map((item) => (
              <li
                key={item.id}
                className="space-y-3 p-3"
              >
                <div className="flex flex-wrap items-center gap-4">
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
                    <p className="font-medium">
                      {item.status === "active" ? (
                        <Link
                          href={show(item.id)}
                          className="underline"
                        >
                          {item.name}
                        </Link>
                      ) : (
                        item.name
                      )}
                    </p>
                    <p className="text-sm text-muted-foreground">
                      {item.owner_profile.shop_name} · {item.category?.name} ·{" "}
                      <span className="font-mono">
                        {formatPeso(item.daily_rate)}/day
                      </span>
                    </p>
                    {item.takedown_reason && (
                      <p className="text-sm">
                        Last reason: {item.takedown_reason}
                      </p>
                    )}
                  </div>
                  <StatusBadge status={item.status} />
                  {item.status === "pending_review" && (
                    <Button
                      onClick={() =>
                        router.patch(
                          ItemReviewController.approve(item.id).url,
                          {},
                          { preserveScroll: true },
                        )
                      }
                    >
                      Approve item
                    </Button>
                  )}
                </div>

                {item.status !== "taken_down" && (
                  <Form
                    {...ItemReviewController.takeDown.form(item.id)}
                    options={{ preserveScroll: true }}
                    className="flex flex-wrap items-start gap-2"
                  >
                    {({ processing, errors }) => (
                      <>
                        <Input
                          name="reason"
                          required
                          placeholder="Reason the owner will see"
                          className="h-8 max-w-md"
                        />
                        <Button
                          variant="outline"
                          disabled={processing}
                        >
                          Take down item
                        </Button>
                        <InputError
                          className="w-full"
                          message={errors.reason}
                        />
                      </>
                    )}
                  </Form>
                )}
              </li>
            ))}
          </ul>
        )}
      </div>
    </>
  );
}

AdminItems.layout = {
  breadcrumbs: [{ title: "Item moderation", href: index() }],
};
