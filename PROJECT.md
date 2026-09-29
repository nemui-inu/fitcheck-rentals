# FitCheck Rentals

Cosplay rental marketplace built for an Advanced Web subject. Owners list costumes, wigs, and props. Renters book them for a date range and pick them up at a meetup. Admins manage users, categories, and inappropriate items.

The project exists to show clean CRUD. Every table has a screen, and every screen does create, read, update, or delete on real data. Business logic stays small on purpose.

## Stack

Laravel 13, Inertia, React, TypeScript, Tailwind v4, shadcn/ui, Fortify, Socialite, Wayfinder, Pest. MySQL 9.7 in Docker for development. Tests run on in memory SQLite.

## Scope

In scope:

1. Accounts and roles: renter by default, owner by opt in, admin by role
2. Google and Facebook login
3. Categories, managed by admins
4. Items with photos and physical units, managed by owners
5. Marketplace browse and item detail
6. Bookings: request, approve, reject, cancel, pick up, return
7. Admin moderation: suspend users, take down items, review resubmissions

Out of scope: payments, deposits handling beyond display, late fees, damage claims, maintenance records, booking history timeline, shipping, reviews, notifications, help page.

## Roles

- **User.** Everyone who signs up. Can browse and rent.
- **Owner.** A user with an owner profile. Unlocks the `/owner` area. Cannot book their own items.
- **Admin.** A user with `role = admin`. Unlocks the `/admin` area. One admin is created by the seeder.

Inertia shares `auth.user`, `isOwner`, and `isAdmin` on every request.

## Authentication

Email and password through Fortify, plus Google and Facebook through Socialite.

Social login callback rules:

- Provider account already linked: log that user in.
- Email is new: create a user with a null password, link the provider, log in.
- Email already exists but this provider is not linked: refuse, and show "An account with this email already exists. Log in with your password, then connect Google in Settings."

Linking and unlinking providers happens in Settings under Connected accounts, while logged in. A user may not unlink their last login method. Users created through social login can set a password in Settings.

Suspended users cannot log in. Check `suspended_at` in the login flow for both password and social login.

## Schema

All money columns are unsigned integers in centavos.

**users**: starter kit columns, plus `password` nullable, `role` (enum, default user), `phone` (nullable string), `suspended_at` (nullable timestamp).

**social_accounts**: id, user_id (FK), provider (enum), provider_user_id (string), timestamps. Unique (provider, provider_user_id). Unique (user_id, provider).

**owner_profiles**: id, user_id (FK, unique), shop_name, meetup_area, timestamps.

**categories**: id, name (unique), code (unique, uppercase, 2 to 6 letters), is_active (bool, default true), timestamps.

**items**: id, owner_profile_id (FK), category_id (FK), name, series (nullable), character (nullable), size (nullable), description (nullable text), daily_rate, deposit, status (enum, default draft), taken_down_at (nullable), taken_down_by (nullable FK users), takedown_reason (nullable text), timestamps. Index on owner_profile_id. Index on (status, category_id).

**item_images**: id, item_id (FK, cascade on delete), path, sort_order (unsigned smallint), timestamps.

**item_units**: id, item_id (FK), label, condition (enum), status (enum, default active), timestamps.

**bookings**: id, reference (ULID, unique), item_unit_id (FK), renter_id (FK users), start_date (date), end_date (date), total, deposit, status (enum, default pending), returned_at (nullable timestamp), timestamps. Index (item_unit_id, status, start_date, end_date).

Foreign keys on rows with history use `restrictOnDelete`. `item_images` cascades with its item.

## Enums

- **Role**: user, admin
- **SocialProvider**: google, facebook
- **ItemStatus**: draft, active, paused, pending_review, taken_down
- **UnitCondition**: new, excellent, good, fair, worn
- **UnitStatus**: active, maintenance, retired
- **BookingStatus**: pending, approved, active, returned, rejected, cancelled

## Rules

### Owners

- Creating an owner profile requires `shop_name`, `meetup_area`, and a phone number on the user.
- Owner queries are scoped through the relation: `$user->ownerProfile->items()->findOrFail($id)`.

### Categories

- Admin only. Full CRUD.
- A category used by any item cannot be deleted, only deactivated. The foreign key enforces this.
- Inactive categories are hidden from the item form but existing items keep them.

### Items

- Only `active` items appear in the marketplace.
- An item can be deleted only if none of its units has a booking. Otherwise pause it.
- The first image by `sort_order` is the cover.
- Photos: local public disk under `items/{item_id}`. Validate `image`, `mimes:jpg,png,webp`, and a size cap. Store the path, never the URL. Write files after commit and delete them with their row.

### Units

- Label is suggested as the category code plus the next number for that owner and category, like `WIG-003`. The owner can edit it.
- Labels are unique per owner, enforced in the Form Request.
- Units are never deleted once booked. Retire them instead.
- Only units with status `active` can be assigned to bookings.

### Takedowns

- Admin takes down an active item with a reason. It leaves the marketplace immediately.
- The owner sees the reason, edits the item, and resubmits. Status becomes `pending_review`.
- Admin approves back to `active` or takes it down again.

### Suspension

- Admin sets `suspended_at`. The user cannot log in. If they own items, those items are hidden from the marketplace while suspended.
- Existing bookings are not changed automatically.

## Bookings

Allowed transitions:

| From     | To                 | Who              |
| -------- | ------------------ | ---------------- |
| pending  | approved, rejected | owner            |
| pending  | cancelled          | renter           |
| approved | active             | owner, at pickup |
| approved | cancelled          | renter or owner  |
| active   | returned           | owner            |

**Request (renter).** Pick an active item and a date range. The renter must have a phone number, or the form asks for one first. The server picks the first unit of that item that is `active` and free for the range and stores it on the booking. If none is free, reject with "Those dates are fully booked. Pick another range." `total` is `RentalDays::count()` times `daily_rate`. `deposit` is copied from the item. One pending request per renter per item.

**Approve (owner).** Inside a transaction, recheck that the assigned unit is still free. If not, switch to another free unit of the same item. If none, fail with a message and leave the booking pending.

**Active and returned (owner).** Mark active at pickup. Mark returned when the item comes back, which sets `returned_at`. The booking page shows days late using `RentalDays::late()`. No fee is charged.

**Availability.** A unit is busy on a date if it is on an `approved` or `active` booking whose range includes that date, or if its status is not `active`. Pending bookings never block.

**Dates.** `start_date` and `end_date` are calendar dates. `returned_at` is a moment. `RentalDays` handles both in `Asia/Manila`. Day counts are inclusive.

## Pages

**Public:** landing, marketplace, item detail.

**Renter:** my bookings, booking detail.

**Owner** (`/owner`): owner profile setup, dashboard with pending requests, items with photos and units, booking requests.

**Admin** (`/admin`): users with suspend and unsuspend, categories CRUD, items needing review and takedown actions.

**Settings:** starter kit profile and password, plus connected accounts.

Marketplace filters live in the query string: search across name, series, and character; category; size; price range. Paginated.

## Theme

Tokens live in `resources/css/app.css`. Zero radius everywhere except avatar, switch, checkbox, radio group, and slider.

**Type.** Bebas Neue for h1 and h2. Roboto for body and h3. Roboto Black for the wordmark. JetBrains Mono for prices, dates, references, and unit labels.

**Logo.** Wordmark `fitcheck.` in Roboto Black, lowercase, pink period. Icon `f.` in ink on a pink square.

**Color layers.** Paper and ink cover about 80% of the UI. Pink covers about 20%: the logo, active nav marker, selected item, and one primary action per screen. Status colors appear only in badges, stamps, and small alerts.

| Status                          | Token       | Style         |
| ------------------------------- | ----------- | ------------- |
| pending, pending_review         | highlight   | filled        |
| approved                        | info        | outlined      |
| active                          | primary     | filled        |
| returned                        | muted       | faded outline |
| rejected, cancelled, taken_down | destructive | outlined      |

Errors use an inverted ink label reading ERROR plus clear wording.

**Motifs.** Offset frame, an outline with offset matching the element's fill, on the primary action, the selected item, and keyboard focus. Square markers at the start of buttons, nav items, and list rows. Halftone dots on the hero and empty states only.

## Copy

- No em dashes anywhere.
- Date ranges use "to": "Oct 12 to Oct 14".
- Sentence case everywhere, buttons included.
- Buttons start with a verb and say what happens.
- Errors say what happened and what to do next.

## Seed data

- `admin@fitcheck.test` with role admin
- `owner@fitcheck.test`, an owner with a shop profile
- `renter@fitcheck.test`, a renter only
- `both@fitcheck.test`, an owner who also rents

All with password `password`. Five categories: Costume (`COS`), Wig (`WIG`), Prop (`PRP`), Accessory (`ACC`), Footwear (`SHO`). About 10 items with 1 to 3 units each, bookings in every status, one item in `pending_review`, and one suspended user.
