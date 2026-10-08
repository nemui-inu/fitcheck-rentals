import { Link, router } from "@inertiajs/react";
import { Head } from "@inertiajs/react";
import CategoryController from "@/actions/App/Http/Controllers/Admin/CategoryController";
import EmptyState from "@/components/empty-state";
import Heading from "@/components/heading";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { index } from "@/routes/admin/categories";
import type { Category } from "@/types";

export default function CategoriesIndex({
  categories,
}: {
  categories: Category[];
}) {
  const destroy = (category: Category) => {
    if (confirm(`Delete ${category.name}? This cannot be undone.`)) {
      router.delete(CategoryController.destroy(category.id).url);
    }
  };

  return (
    <>
      <Head title="Categories" />

      <div className="space-y-6 px-4 py-6">
        <div className="flex items-start justify-between gap-4">
          <Heading
            title="Categories"
            description="Owners pick one of these for every item."
          />
          <Button asChild>
            <Link href={CategoryController.create()}>Add a category</Link>
          </Button>
        </div>

        {categories.length === 0 ? (
          <EmptyState
            title="No categories yet"
            description="Add a category so owners can list items."
          />
        ) : (
          <table className="w-full border text-sm">
            <thead className="bg-muted text-left">
              <tr>
                <th className="p-3 font-medium">Name</th>
                <th className="p-3 font-medium">Code</th>
                <th className="p-3 font-medium">Status</th>
                <th className="p-3" />
              </tr>
            </thead>
            <tbody>
              {categories.map((category) => (
                <tr
                  key={category.id}
                  className="border-t"
                >
                  <td className="p-3">{category.name}</td>
                  <td className="p-3 font-mono">{category.code}</td>
                  <td className="p-3">
                    {category.is_active ? (
                      <Badge variant="outline">Active</Badge>
                    ) : (
                      <Badge
                        variant="outline"
                        className="text-muted-foreground"
                      >
                        Inactive
                      </Badge>
                    )}
                  </td>
                  <td className="space-x-2 p-3 text-right">
                    <Button
                      variant="outline"
                      asChild
                    >
                      <Link href={CategoryController.edit(category.id)}>
                        Edit
                      </Link>
                    </Button>
                    <Button
                      variant="ghost"
                      onClick={() => destroy(category)}
                    >
                      Delete
                    </Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </>
  );
}

CategoriesIndex.layout = {
  breadcrumbs: [{ title: "Categories", href: index() }],
};
