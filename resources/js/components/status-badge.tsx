import { cn } from "@/lib/utils";

const styles: Record<string, string> = {
  pending: "border-highlight bg-highlight text-highlight-foreground",
  pending_review: "border-highlight bg-highlight text-highlight-foreground",
  approved: "border-info text-info",
  active: "border-primary bg-primary text-primary-foreground",
  returned: "border-muted-foreground/40 text-muted-foreground",
  rejected: "border-destructive text-destructive",
  cancelled: "border-destructive text-destructive",
  taken_down: "border-destructive text-destructive",
  retired: "border-muted-foreground/40 text-muted-foreground",
};

export default function StatusBadge({ status }: { status: string }) {
  return (
    <span
      className={cn(
        "inline-flex items-center border px-2 py-0.5 font-mono text-xs uppercase",
        styles[status] ?? "border-border text-foreground",
      )}
    >
      {status.replace("_", " ")}
    </span>
  );
}
