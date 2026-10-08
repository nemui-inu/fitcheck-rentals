import { Head, Link, router } from "@inertiajs/react";
import type { FormEvent } from "react";
import UserController from "@/actions/App/Http/Controllers/Admin/UserController";
import Heading from "@/components/heading";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { index } from "@/routes/admin/users";
import type { Paginated } from "@/types";

type AdminUser = {
  id: number;
  name: string;
  email: string;
  role: "user" | "admin";
  suspended_at: string | null;
  owner_profile: { shop_name: string } | null;
};

export default function UsersIndex({
  users,
  search,
}: {
  users: Paginated<AdminUser>;
  search: string | null;
}) {
  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const value = new FormData(event.currentTarget).get("search");
    const search = typeof value === "string" ? value : "";
    router.get(index().url, search ? { search } : {}, {
      preserveState: true,
    });
  };

  const toggle = (user: AdminUser) => {
    if (user.suspended_at) {
      router.patch(
        UserController.unsuspend(user.id).url,
        {},
        { preserveScroll: true },
      );
    } else if (
      confirm(
        `Suspend ${user.name}? They are logged out and their items are hidden.`,
      )
    ) {
      router.patch(
        UserController.suspend(user.id).url,
        {},
        { preserveScroll: true },
      );
    }
  };

  return (
    <>
      <Head title="Users" />

      <div className="space-y-6 px-4 py-6">
        <Heading
          title="Users"
          description="Suspend accounts that break the rules."
        />

        <form
          onSubmit={submit}
          className="flex max-w-md gap-2"
        >
          <Input
            name="search"
            defaultValue={search ?? ""}
            placeholder="Search name or email"
          />
          <Button variant="outline">Search users</Button>
        </form>

        <table className="w-full border text-sm">
          <thead className="bg-muted text-left">
            <tr>
              <th className="p-3 font-medium">Name</th>
              <th className="p-3 font-medium">Email</th>
              <th className="p-3 font-medium">Role</th>
              <th className="p-3 font-medium">Status</th>
              <th className="p-3" />
            </tr>
          </thead>
          <tbody>
            {users.data.map((user) => (
              <tr
                key={user.id}
                className="border-t"
              >
                <td className="p-3">{user.name}</td>
                <td className="p-3">{user.email}</td>
                <td className="p-3">
                  {user.role === "admin"
                    ? "Admin"
                    : user.owner_profile
                      ? `Owner, ${user.owner_profile.shop_name}`
                      : "Renter"}
                </td>
                <td className="p-3">
                  {user.suspended_at ? (
                    <span className="border border-destructive px-2 py-0.5 font-mono text-xs text-destructive uppercase">
                      suspended
                    </span>
                  ) : (
                    "Active"
                  )}
                </td>
                <td className="p-3 text-right">
                  {user.role !== "admin" && (
                    <Button
                      variant="outline"
                      onClick={() => toggle(user)}
                    >
                      {user.suspended_at ? "Unsuspend user" : "Suspend user"}
                    </Button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {users.last_page > 1 && (
          <div className="flex justify-between">
            {users.prev_page_url ? (
              <Link href={users.prev_page_url}>Previous page</Link>
            ) : (
              <span />
            )}
            {users.next_page_url && (
              <Link href={users.next_page_url}>Next page</Link>
            )}
          </div>
        )}
      </div>
    </>
  );
}

UsersIndex.layout = {
  breadcrumbs: [{ title: "Users", href: index() }],
};
