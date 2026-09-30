import { Form, Head, router } from "@inertiajs/react";
import { ArrowDown, ArrowUp, Trash2 } from "lucide-react";
import ItemController from "@/actions/App/Http/Controllers/Owner/ItemController";
import ItemImageController from "@/actions/App/Http/Controllers/Owner/ItemImageController";
import ItemStatusController, {
  resubmit,
} from "@/actions/App/Http/Controllers/Owner/ItemStatusController";
import ItemUnitController from "@/actions/App/Http/Controllers/Owner/ItemUnitController";
import Heading from "@/components/heading";
import InputError from "@/components/input-error";
import ItemFields from "@/components/owner/item-fields";
import StatusBadge from "@/components/status-badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { index } from "@/routes/owner/items";
import type { Category, Item, UnitCondition, UnitStatus } from "@/types";

const conditions: UnitCondition[] = [
  "new",
  "excellent",
  "good",
  "fair",
  "worn",
];
const unitStatuses: UnitStatus[] = ["active", "maintenance", "retired"];

const selectClass = "h-8 border border-input bg-transparent px-2 text-sm";

function StatusActions({ item }: { item: Item }) {
  const setStatus = (status: "active" | "paused") =>
    router.patch(
      ItemStatusController(item.id).url,
      { status },
      { preserveScroll: true },
    );

  return (
    <div className="flex flex-wrap items-center gap-3">
      <StatusBadge status={item.status} />
      {(item.status === "draft" || item.status === "paused") && (
        <Button
          size="sm"
          onClick={() => setStatus("active")}
        >
          Publish to marketplace
        </Button>
      )}
      {item.status === "active" && (
        <Button
          size="sm"
          variant="outline"
          onClick={() => setStatus("paused")}
        >
          Pause listing
        </Button>
      )}
      {item.status === "taken_down" && (
        <Button
          size="sm"
          onClick={() =>
            router.patch(resubmit(item.id).url, {}, { preserveScroll: true })
          }
        >
          Resubmit for review
        </Button>
      )}
    </div>
  );
}

export default function ItemEdit({
  item,
  categories,
  suggestedLabel,
  errors,
}: {
  item: Item;
  categories: Category[];
  suggestedLabel: string;
  errors: Partial<Record<string, string>>;
}) {
  const images = item.images ?? [];
  const units = item.units ?? [];

  const destroyItem = () => {
    if (confirm(`Delete ${item.name}? This cannot be undone.`)) {
      router.delete(ItemController.destroy(item.id).url);
    }
  };

  return (
    <>
      <Head title={item.name} />

      <div className="max-w-4xl space-y-10 px-4 py-6">
        <div className="space-y-3">
          <Heading title={item.name} />
          <StatusActions item={item} />
          <InputError message={errors.status} />
          {item.takedown_reason && (
            <p className="border border-destructive p-3 text-sm">
              <span className="mr-2 bg-foreground px-1.5 py-0.5 font-mono text-xs text-background">
                ERROR
              </span>
              Taken down: {item.takedown_reason}. Fix the item, then resubmit it
              for review.
            </p>
          )}
        </div>

        <section className="space-y-4">
          <h3>Details</h3>
          <Form
            {...ItemController.update.form(item.id)}
            options={{ preserveScroll: true }}
            className="space-y-6"
          >
            {({ processing, errors: formErrors }) => (
              <>
                <ItemFields
                  item={item}
                  categories={categories}
                  errors={formErrors}
                />
                <Button
                  variant="outline"
                  disabled={processing}
                >
                  Save details
                </Button>
              </>
            )}
          </Form>
        </section>

        <section className="space-y-4">
          <h3>Photos</h3>
          <p className="text-sm text-muted-foreground">
            The first photo is the cover. JPG, PNG, or WEBP up to 4 MB.
          </p>

          {images.length > 0 && (
            <ul className="grid grid-cols-2 gap-3 sm:grid-cols-4">
              {images.map((image, position) => (
                <li
                  key={image.id}
                  className="space-y-2 border p-2"
                >
                  <img
                    src={image.url}
                    alt=""
                    className="aspect-square w-full object-cover"
                  />
                  <div className="flex items-center justify-between">
                    <span className="font-mono text-xs">
                      {position === 0 ? "COVER" : `#${position + 1}`}
                    </span>
                    <div className="flex">
                      <Button
                        size="icon"
                        variant="ghost"
                        aria-label="Move photo up"
                        disabled={position === 0}
                        onClick={() =>
                          router.patch(
                            ItemImageController.update([item.id, image.id]).url,
                            { direction: "up" },
                            { preserveScroll: true },
                          )
                        }
                      >
                        <ArrowUp size={16} />
                      </Button>
                      <Button
                        size="icon"
                        variant="ghost"
                        aria-label="Move photo down"
                        disabled={position === images.length - 1}
                        onClick={() =>
                          router.patch(
                            ItemImageController.update([item.id, image.id]).url,
                            { direction: "down" },
                            { preserveScroll: true },
                          )
                        }
                      >
                        <ArrowDown size={16} />
                      </Button>
                      <Button
                        size="icon"
                        variant="ghost"
                        aria-label="Delete photo"
                        onClick={() =>
                          router.delete(
                            ItemImageController.destroy([item.id, image.id])
                              .url,
                            { preserveScroll: true },
                          )
                        }
                      >
                        <Trash2 size={16} />
                      </Button>
                    </div>
                  </div>
                </li>
              ))}
            </ul>
          )}

          <Form
            {...ItemImageController.store.form(item.id)}
            options={{ preserveScroll: true }}
            resetOnSuccess
            className="flex flex-wrap items-start gap-3"
          >
            {({ processing, errors: formErrors }) => (
              <>
                <Input
                  type="file"
                  name="photos[]"
                  accept="image/jpeg,image/png,image/webp"
                  multiple
                  required
                  className="max-w-sm"
                />
                <Button
                  variant="outline"
                  disabled={processing}
                >
                  Upload photos
                </Button>
                <InputError
                  className="w-full"
                  message={
                    formErrors.photos ??
                    Object.entries(formErrors).find(([key]) =>
                      key.startsWith("photos."),
                    )?.[1]
                  }
                />
              </>
            )}
          </Form>
        </section>

        <section className="space-y-4">
          <h3>Units</h3>
          <p className="text-sm text-muted-foreground">
            Each unit is one physical copy renters can book.
          </p>

          {units.length > 0 && (
            <ul className="divide-y border">
              {units.map((unit) => (
                <li key={unit.id}>
                  <Form
                    {...ItemUnitController.update.form([item.id, unit.id])}
                    options={{ preserveScroll: true }}
                    className="flex flex-wrap items-center gap-3 p-3"
                  >
                    {({ processing, errors: formErrors }) => (
                      <>
                        <Input
                          name="label"
                          defaultValue={unit.label}
                          className="h-8 w-32 font-mono"
                        />
                        <select
                          name="condition"
                          defaultValue={unit.condition}
                          className={selectClass}
                        >
                          {conditions.map((condition) => (
                            <option key={condition}>{condition}</option>
                          ))}
                        </select>
                        <select
                          name="status"
                          defaultValue={unit.status}
                          className={selectClass}
                        >
                          {unitStatuses.map((status) => (
                            <option key={status}>{status}</option>
                          ))}
                        </select>
                        <Button
                          size="sm"
                          variant="outline"
                          disabled={processing}
                        >
                          Save unit
                        </Button>
                        <Button
                          type="button"
                          size="sm"
                          variant="ghost"
                          onClick={() =>
                            router.delete(
                              ItemUnitController.destroy([item.id, unit.id])
                                .url,
                              { preserveScroll: true },
                            )
                          }
                        >
                          Delete unit
                        </Button>
                        <InputError
                          className="w-full"
                          message={formErrors.label}
                        />
                      </>
                    )}
                  </Form>
                </li>
              ))}
            </ul>
          )}

          <Form
            {...ItemUnitController.store.form(item.id)}
            options={{ preserveScroll: true }}
            resetOnSuccess
            className="flex flex-wrap items-center gap-3"
          >
            {({ processing, errors: formErrors }) => (
              <>
                <Input
                  name="label"
                  defaultValue={suggestedLabel}
                  className="h-8 w-32 font-mono"
                  aria-label="Unit label"
                />
                <select
                  name="condition"
                  defaultValue="good"
                  className={selectClass}
                  aria-label="Condition"
                >
                  {conditions.map((condition) => (
                    <option key={condition}>{condition}</option>
                  ))}
                </select>
                <Button
                  size="sm"
                  variant="outline"
                  disabled={processing}
                >
                  Add unit
                </Button>
                <InputError
                  className="w-full"
                  message={formErrors.label ?? formErrors.condition}
                />
              </>
            )}
          </Form>
        </section>

        <section className="space-y-2 border-t pt-6">
          <Button
            variant="ghost"
            onClick={destroyItem}
          >
            Delete item
          </Button>
        </section>
      </div>
    </>
  );
}

ItemEdit.layout = {
  breadcrumbs: [{ title: "My items", href: index() }],
};
