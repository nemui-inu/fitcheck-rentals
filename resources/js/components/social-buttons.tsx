import { Button } from "@/components/ui/button";
import { redirect } from "@/routes/social";

const providers = [
  { value: "google", label: "Google" },
  { value: "facebook", label: "Facebook" },
];

export default function SocialButtons() {
  return (
    <div className="space-y-3">
      <div className="flex items-center gap-3 text-xs text-muted-foreground uppercase">
        <span className="h-px flex-1 bg-border" />
        or
        <span className="h-px flex-1 bg-border" />
      </div>
      <div className="grid grid-cols-2 gap-3">
        {providers.map((provider) => (
          <Button
            key={provider.value}
            variant="outline"
            asChild
          >
            <a href={redirect(provider.value).url}>
              Continue with {provider.label}
            </a>
          </Button>
        ))}
      </div>
    </div>
  );
}
