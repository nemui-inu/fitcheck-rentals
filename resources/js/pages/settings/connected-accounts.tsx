import { Form, Head, router } from "@inertiajs/react";
import ConnectedAccountController from "@/actions/App/Http/Controllers/Settings/ConnectedAccountController";
import Heading from "@/components/heading";
import InputError from "@/components/input-error";
import PasswordInput from "@/components/password-input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { edit } from "@/routes/connected-accounts";
import { redirect } from "@/routes/social";

type Provider = { value: string; label: string; connected: boolean };

export default function ConnectedAccounts({
  providers,
  hasPassword,
  passwordRules,
  errors,
}: {
  providers: Provider[];
  hasPassword: boolean;
  passwordRules: string;
  errors: Partial<Record<string, string>>;
}) {
  return (
    <>
      <Head title="Connected accounts" />

      <h1 className="sr-only">Connected accounts</h1>

      <div className="space-y-6">
        <Heading
          variant="small"
          title="Connected accounts"
          description="Log in with Google or Facebook as well as your password."
        />

        <ul className="divide-y border">
          {providers.map((provider) => (
            <li
              key={provider.value}
              className="flex items-center justify-between p-3"
            >
              <div>
                <p className="font-medium">{provider.label}</p>
                <p className="text-sm text-muted-foreground">
                  {provider.connected ? "Connected" : "Not connected"}
                </p>
              </div>
              {provider.connected ? (
                <Button
                  variant="outline"
                  onClick={() =>
                    router.delete(
                      ConnectedAccountController.destroy(provider.value).url,
                      { preserveScroll: true },
                    )
                  }
                >
                  Disconnect {provider.label}
                </Button>
              ) : (
                <Button
                  variant="outline"
                  asChild
                >
                  <a href={redirect(provider.value).url}>
                    Connect {provider.label}
                  </a>
                </Button>
              )}
            </li>
          ))}
        </ul>
        <InputError message={errors.provider} />
      </div>

      {!hasPassword && (
        <div className="space-y-6">
          <Heading
            variant="small"
            title="Set a password"
            description="You signed up with a provider. Add a password to log in with your email too."
          />

          <Form
            {...ConnectedAccountController.setPassword.form()}
            options={{ preserveScroll: true }}
            resetOnSuccess
            className="space-y-6"
          >
            {({ processing, errors: formErrors }) => (
              <>
                <div className="grid gap-2">
                  <Label htmlFor="password">Password</Label>
                  <PasswordInput
                    id="password"
                    name="password"
                    autoComplete="new-password"
                    passwordrules={passwordRules}
                  />
                  <InputError message={formErrors.password} />
                </div>
                <div className="grid gap-2">
                  <Label htmlFor="password_confirmation">
                    Confirm password
                  </Label>
                  <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    autoComplete="new-password"
                  />
                </div>
                <Button disabled={processing}>Set password</Button>
              </>
            )}
          </Form>
        </div>
      )}
    </>
  );
}

ConnectedAccounts.layout = {
  breadcrumbs: [{ title: "Connected accounts", href: edit() }],
};
