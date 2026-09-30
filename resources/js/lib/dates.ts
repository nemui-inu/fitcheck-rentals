const short = new Intl.DateTimeFormat("en-PH", {
  month: "short",
  day: "numeric",
});

function parseCalendarDate(value: string): Date {
  const [year, month, day] = value.slice(0, 10).split("-").map(Number);

  return new Date(year, month - 1, day);
}

export function formatDate(value: string): string {
  return short.format(parseCalendarDate(value));
}

export function formatRange(start: string, end: string): string {
  return start.slice(0, 10) === end.slice(0, 10)
    ? formatDate(start)
    : `${formatDate(start)} to ${formatDate(end)}`;
}
