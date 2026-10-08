import { Head, Link, usePage } from "@inertiajs/react";
import AppLogoIcon from "@/components/app-logo-icon";
import { Button } from "@/components/ui/button";
import { dashboard, login, register } from "@/routes";
import { index as marketplace } from "@/routes/marketplace";
import { setup as ownerSetup } from "@/routes/owner";

const steps = [
  {
    title: "Find the fit",
    body: "Browse costumes, wigs, and props by series, character, size, and price.",
  },
  {
    title: "Request your dates",
    body: "Pick a date range. The owner approves and a unit is held for you.",
  },
  {
    title: "Meet up and suit up",
    body: "Pick it up at the owner's meetup spot, wear it, bring it back.",
  },
];

export default function Welcome() {
  const { auth } = usePage().props;

  return (
    <>
      <Head title="Cosplay rentals" />

      <div className="min-h-screen bg-background text-foreground">
        <header className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
          <Link
            href="/"
            className="flex items-center gap-2"
          >
            <AppLogoIcon className="size-8" />
            <span className="font-brand text-xl font-black tracking-[-0.03em]">
              fitcheck<span className="text-primary">.</span>
            </span>
          </Link>
          <nav className="flex items-center gap-2 text-sm">
            <Button
              variant="ghost"
              asChild
            >
              <Link href={marketplace()}>Marketplace</Link>
            </Button>
            {auth.user ? (
              <Button
                variant="outline"
                asChild
              >
                <Link href={dashboard()}>Go to dashboard</Link>
              </Button>
            ) : (
              <>
                <Button
                  variant="ghost"
                  asChild
                >
                  <Link href={login()}>Log in</Link>
                </Button>
                <Button
                  variant="outline"
                  asChild
                >
                  <Link href={register()}>Sign up</Link>
                </Button>
              </>
            )}
          </nav>
        </header>

        <main className="mx-auto max-w-6xl px-4">
          <section className="relative overflow-hidden border-4 border-foreground px-6 py-16 md:px-12 md:py-24">
            <div
              aria-hidden
              className="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(var(--color-primary)_1.5px,transparent_1.5px)] [mask-image:linear-gradient(to_left,black,transparent)] [background-size:10px_10px] opacity-60"
            />
            <div className="relative max-w-2xl space-y-6">
              <span className="inline-block -rotate-2 border-2 border-foreground bg-highlight px-3 py-1 font-mono text-xs text-highlight-foreground uppercase">
                Con season is open
              </span>
              <h1 className="text-6xl md:text-8xl">
                Rent the fit. Skip the sewing
                <span className="text-primary">.</span>
              </h1>
              <p className="max-w-lg text-lg text-muted-foreground">
                FitCheck connects cosplayers with local owners who rent out
                costumes, wigs, and props by the day.
              </p>
              <div className="flex flex-wrap gap-4">
                <Button
                  asChild
                  className="outline-[1.5px] outline-offset-3 outline-primary outline-solid"
                >
                  <Link href={marketplace()}>Browse the marketplace</Link>
                </Button>
                <Button
                  variant="outline"
                  asChild
                >
                  <Link href={auth.user ? ownerSetup() : register()}>
                    List your costumes
                  </Link>
                </Button>
              </div>
            </div>
          </section>

          <section className="grid gap-4 py-12 md:grid-cols-3">
            {steps.map((step, index) => (
              <div
                key={step.title}
                className="space-y-2 border p-6"
              >
                <p className="font-mono text-sm text-muted-foreground">
                  0{index + 1}
                </p>
                <h2>{step.title}</h2>
                <p className="text-muted-foreground">{step.body}</p>
              </div>
            ))}
          </section>
        </main>
      </div>
    </>
  );
}
