# fitcheck.

A cosplay rental marketplace. Owners list costumes, wigs, and props with photos and physical units. Renters book them for a date range and pick them up at a meetup. Admins manage users, categories, and items that break the rules.

Built for an Advanced Web subject to show clean CRUD across a small, real app.

## Features

**Renters**

- Browse and search the marketplace by category, size, and price
- Request an item for a date range
- Track and cancel bookings

**Owners**

- Set up a shop profile
- Create items with photos and labeled units like `WIG-001`
- Approve or reject requests, mark pickups and returns

**Admins**

- Manage categories
- Suspend and unsuspend users
- Take down items and review resubmissions

**Everyone**

- Sign in with email and password, Google, or Facebook
- Light and dark mode

## Stack

Laravel 13, Inertia, React, TypeScript, Tailwind CSS v4, shadcn/ui, Fortify, Socialite, Pest. MySQL 9.7 in Docker for development, in memory SQLite for tests.

## Requirements

- PHP 8.3 or newer with the `intl`, `pdo_mysql`, and `pdo_sqlite` extensions
- Composer 2
- Node 22
- Docker with Compose

## Getting started

```bash
git clone <repo-url> fitcheck-rentals
cd fitcheck-rentals
composer run setup
php artisan storage:link
php artisan db:seed
composer run dev
```

`php artisan storage:link` makes uploaded item photos public. `composer run setup` installs dependencies, creates `.env` from `.env.example`, generates the app key, starts MySQL in Docker, runs migrations, and builds the frontend. `composer run dev` starts MySQL, the app server, the queue, logs, and Vite together.

Open http://127.0.0.1:8000.

### Demo accounts

All use the password `password`.

| Email                     | Role                                                     |
| ------------------------- | -------------------------------------------------------- |
| `admin@fitcheck.test`     | Admin                                                    |
| `owner@fitcheck.test`     | Owner                                                    |
| `renter@fitcheck.test`    | Renter                                                   |
| `both@fitcheck.test`      | Owner who also rents                                     |
| `suspended@fitcheck.test` | Suspended owner. Cannot log in, and their item is hidden |

### Social login

Google and Facebook need app credentials in `.env`:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
```

Users link and unlink providers under Settings, Connected accounts. Set the redirect URLs in each provider's console to:

- `http://127.0.0.1:8000/auth/google/callback`
- `http://127.0.0.1:8000/auth/facebook/callback`

## Scripts

| Command                 | What it does                            |
| ----------------------- | --------------------------------------- |
| `composer run dev`      | Start everything for local development  |
| `composer run dev:down` | Stop the MySQL container                |
| `composer run dev:nuke` | Stop MySQL and delete its data volume   |
| `composer test`         | Lint check, static analysis, and tests  |
| `composer ci:check`     | Everything CI runs, frontend included   |
| `composer lint`         | Fix PHP formatting with Pint            |
| `npx vp check --fix`    | Fix frontend formatting and lint issues |

## Project docs

- [`PROJECT.md`](PROJECT.md): scope, schema, rules, and design system
- [`BACKLOG.md`](BACKLOG.md): build order and progress
- [`COMMITS.md`](COMMITS.md): commit message conventions
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md): code map, request flow, where rules live, and gotchas
- [`docs/DIAGRAMS.md`](docs/DIAGRAMS.md): ERD and user flows
