import { Form, Head } from "@inertiajs/react";
import CategoryController from "@/actions/App/Http/Controllers/Admin/CategoryController";
import Heading from "@/components/heading";
import InputError from "@/components/input-error";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { index } from "@/routes/admin/categories";
import type { Category } from "@/types";

export default function CategoryForm({
  category,
}: {
  category: Category | null;
}) {
  const action = category
    ? CategoryController.update.form(category.id)
    : CategoryController.store.form();

  return (
    <>
      <Head title={category ? "Edit category" : "Add a category"} />

      <div className="max-w-xl space-y-6 px-4 py-6">
        <Heading title={category ? "Edit category" : "Add a category"} />

        <Form
          {...action}
          className="space-y-6"
        >
          {({ processing, errors }) => (
            <>
              <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input
                  id="name"
                  name="name"
                  required
                  defaultValue={category?.name}
                  placeholder="Wig"
                />
                <InputError message={errors.name} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="code">Code</Label>
                <Input
                  id="code"
                  name="code"
                  required
                  maxLength={6}
                  defaultValue={category?.code}
                  placeholder="WIG"
                  className="font-mono uppercase"
                />
                <p className="text-sm text-muted-foreground">
                  2 to 6 letters. Unit labels start with it, like WIG-003.
                </p>
                <InputError message={errors.code} />
              </div>

              {category && (
                <label className="flex items-center gap-2 text-sm">
                  <input
                    type="hidden"
                    name="is_active"
                    value="0"
                  />
                  <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    defaultChecked={category.is_active}
                  />
                  Active. Inactive categories are hidden from the item form.
                </label>
              )}

              <Button disabled={processing}>
                {category ? "Save category" : "Add category"}
              </Button>
            </>
          )}
        </Form>
      </div>
    </>
  );
}

CategoryForm.layout = {
  breadcrumbs: [{ title: "Categories", href: index() }],
};
