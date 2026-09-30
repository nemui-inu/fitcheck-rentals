import { Form, Head } from "@inertiajs/react";
import ItemController from "@/actions/App/Http/Controllers/Owner/ItemController";
import Heading from "@/components/heading";
import ItemFields from "@/components/owner/item-fields";
import { Button } from "@/components/ui/button";
import { index } from "@/routes/owner/items";
import type { Category } from "@/types";

export default function ItemCreate({ categories }: { categories: Category[] }) {
  return (
    <>
      <Head title="Add an item" />

      <div className="max-w-3xl space-y-6 px-4 py-6">
        <Heading
          title="Add an item"
          description="It starts as a draft. Add photos and units next."
        />

        <Form
          {...ItemController.store.form()}
          className="space-y-6"
        >
          {({ processing, errors }) => (
            <>
              <ItemFields
                categories={categories}
                errors={errors}
              />
              <Button disabled={processing}>Save draft</Button>
            </>
          )}
        </Form>
      </div>
    </>
  );
}

ItemCreate.layout = {
  breadcrumbs: [{ title: "My items", href: index() }],
};
