# Commit conventions

FitCheck follows [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/).

## Format

```
type(scope): description

optional body

optional footer
```

- Lowercase, imperative mood: "add", not "added" or "adds"
- No period at the end
- Subject under 72 characters
- No em dashes anywhere in the message

## Types

| Type       | Use for                                          | Example                                                       |
| ---------- | ------------------------------------------------ | ------------------------------------------------------------- |
| `feat`     | A new user facing feature                        | `feat(booking): add approval with unit assignment`            |
| `fix`      | A bug fix                                        | `fix(availability): include turnaround days in overlap check` |
| `test`     | Adding or fixing tests only                      | `test(booking): cover double approval race`                   |
| `refactor` | Code change with no behavior change              | `refactor(payments): extract refund calculation`              |
| `style`    | Formatting, Pint, whitespace. Not CSS            | `style: run pint`                                             |
| `docs`     | Docs, help page content, README                  | `docs(help): add damage section`                              |
| `perf`     | Performance improvement                          | `perf(marketplace): add fulltext index on items`              |
| `build`    | Dependencies, Vite, build config                 | `build: bump inertia to v3`                                   |
| `ci`       | CI pipelines                                     | `ci: add test workflow`                                       |
| `chore`    | Tooling and housekeeping that fits nothing above | `chore(docker): add mysql healthcheck`                        |

UI styling work (theme, components, layouts) is `feat` when users see something new, `fix` when correcting how it looks, `refactor` when only cleaning code.

## Scopes

Optional, but keep them consistent. Use these:

| Scope          | Covers                                         |
| -------------- | ---------------------------------------------- |
| `owner`        | Owner mode, owner profile, owner middleware    |
| `items`        | Items, units, photos                           |
| `listings`     | Listings and composition                       |
| `marketplace`  | Browse, search, filters                        |
| `booking`      | Requests, approval, transitions, booking pages |
| `availability` | `AvailabilityService` and calendar blocking    |
| `payments`     | Checkout, mock gateway, payment records        |
| `returns`      | Hand over, return, late fees, damage           |
| `maintenance`  | Maintenance records and queue                  |
| `dashboard`    | Owner dashboard                                |
| `help`         | Help page                                      |
| `theme`        | Tokens, fonts, icons, logo                     |
| `auth`         | Login, registration, settings                  |
| `db`           | Migrations, seeders, factories                 |
| `docker`       | `docker-compose.yml`                           |
| `composer`     | `composer.json` scripts                        |
| `env`          | `.env.example`                                 |

Leave the scope off when a change spans many areas.

## Body

Add one when the why is not obvious from the subject. Wrap at 72 characters. Explain the reason, not the diff.

```
fix(booking): lock units before reading availability

MySQL REPEATABLE READ freezes the snapshot at the first plain
SELECT, so checking overlap before taking the lock could read
stale bookings.
```

## Breaking changes

Add `!` after the type or scope, and a `BREAKING CHANGE:` footer.

```
feat(db)!: store money as centavos

BREAKING CHANGE: all money columns are now integers. Run
migrate:fresh.
```

## Habits

- One logical change per commit. Split "add listings and fix calendar" into two.
- Commit at the end of each backlog item, at minimum.
- Never commit `.env`. Commit new keys to `.env.example` with placeholder values.
- Run `composer test` before committing anything under `app/`.
