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

## API

A read-only JSON API for the marketplace lives at `/api/v1`. It is rate limited to 60 requests per minute per IP, and it never returns drafts or items from suspended owners. Money is returned as integer centavos, so `35000` means ₱350.00.

| Method | Path                              | What it returns                                                                  |
| ------ | --------------------------------- | -------------------------------------------------------------------------------- |
| GET    | `/api/v1/items`                   | Paginated items. Filters: `search`, `category`, `size`, `min_price`, `max_price` |
| GET    | `/api/v1/items/{id}`              | One item with category, images, owner, and free unit count                       |
| GET    | `/api/v1/items/{id}/availability` | Whether the item has a free unit for a `start` and `end` date range              |
| GET    | `/api/v1/categories`              | Active categories                                                                |

### Examples

```bash
# Search the marketplace
curl "http://127.0.0.1:8000/api/v1/items?search=wig&category=WIG&min_price=200"

# One item
curl http://127.0.0.1:8000/api/v1/items/1

# Check a date range, returns 422 if a date is invalid
curl "http://127.0.0.1:8000/api/v1/items/1/availability?start=2026-10-12&end=2026-10-14"

# Active categories
curl http://127.0.0.1:8000/api/v1/categories
```

`GET /api/v1/items/1`:

```json
{
  "data": {
    "id": 1,
    "name": "Frieren wig",
    "series": "Frieren: Beyond Journey's End",
    "character": "Fern",
    "size": "M",
    "description": "Shoulder length silver wig with bangs.",
    "daily_rate": 35000,
    "deposit": 100000,
    "category": { "id": 2, "name": "Wig", "code": "WIG" },
    "cover_image": "http://127.0.0.1:8000/storage/items/1/cover.jpg",
    "images": [
      {
        "id": 4,
        "sort_order": 0,
        "url": "http://127.0.0.1:8000/storage/items/1/cover.jpg"
      }
    ],
    "owner": { "shop_name": "Moonlit Cosplays", "meetup_area": "Quezon City" },
    "available_units_count": 2
  }
}
```

`GET /api/v1/items/1/availability?start=2026-10-12&end=2026-10-14`:

```json
{
  "start": "2026-10-12",
  "end": "2026-10-14",
  "available": true,
  "free_units": 2
}
```

`GET /api/v1/categories`:

```json
{
  "data": [
    { "id": 1, "name": "Costume", "code": "COS" },
    { "id": 2, "name": "Wig", "code": "WIG" }
  ]
}
```

## Project docs

- [`PROJECT.md`](PROJECT.md): scope, schema, rules, and design system
- [`BACKLOG.md`](BACKLOG.md): build order and progress
- [`COMMITS.md`](COMMITS.md): commit message conventions
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md): code map, request flow, where rules live, and gotchas
- [`docs/DIAGRAMS.md`](docs/DIAGRAMS.md): ERD and user flows
