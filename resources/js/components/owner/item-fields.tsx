import InputError from "@/components/input-error";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import type { Category, Item } from "@/types";

type Errors = Partial<Record<string, string>>;

function Field({
  name,
  label,
  errors,
  children,
}: {
  name: string;
  label: string;
  errors: Errors;
  children: React.ReactNode;
}) {
  return (
    <div className="grid gap-2">
      <Label htmlFor={name}>{label}</Label>
      {children}
      <InputError message={errors[name]} />
    </div>
  );
}

export default function ItemFields({
  item,
  categories,
  errors,
}: {
  item?: Item;
  categories: Category[];
  errors: Errors;
}) {
  return (
    <div className="grid gap-6 md:grid-cols-2">
      <Field
        name="name"
        label="Name"
        errors={errors}
      >
        <Input
          id="name"
          name="name"
          required
          defaultValue={item?.name}
          placeholder="Frieren full costume"
        />
      </Field>

      <Field
        name="category_id"
        label="Category"
        errors={errors}
      >
        <select
          id="category_id"
          name="category_id"
          required
          defaultValue={item?.category_id ?? ""}
          className="h-9 border border-input bg-transparent px-3 text-sm"
        >
          <option
            value=""
            disabled
          >
            Pick a category
          </option>
          {categories.map((category) => (
            <option
              key={category.id}
              value={category.id}
            >
              {category.name}
            </option>
          ))}
        </select>
      </Field>

      <Field
        name="series"
        label="Series"
        errors={errors}
      >
        <Input
          id="series"
          name="series"
          defaultValue={item?.series ?? ""}
        />
      </Field>

      <Field
        name="character"
        label="Character"
        errors={errors}
      >
        <Input
          id="character"
          name="character"
          defaultValue={item?.character ?? ""}
        />
      </Field>

      <Field
        name="size"
        label="Size"
        errors={errors}
      >
        <Input
          id="size"
          name="size"
          defaultValue={item?.size ?? ""}
          placeholder="M"
        />
      </Field>

      <div />

      <Field
        name="daily_rate"
        label="Daily rate (₱)"
        errors={errors}
      >
        <Input
          id="daily_rate"
          name="daily_rate"
          type="number"
          min="1"
          step="0.01"
          required
          defaultValue={item ? item.daily_rate / 100 : ""}
          className="font-mono"
        />
      </Field>

      <Field
        name="deposit"
        label="Deposit (₱)"
        errors={errors}
      >
        <Input
          id="deposit"
          name="deposit"
          type="number"
          min="0"
          step="0.01"
          required
          defaultValue={item ? item.deposit / 100 : ""}
          className="font-mono"
        />
      </Field>

      <div className="md:col-span-2">
        <Field
          name="description"
          label="Description"
          errors={errors}
        >
          <textarea
            id="description"
            name="description"
            rows={4}
            defaultValue={item?.description ?? ""}
            className="border border-input bg-transparent px-3 py-2 text-sm"
          />
        </Field>
      </div>
    </div>
  );
}
