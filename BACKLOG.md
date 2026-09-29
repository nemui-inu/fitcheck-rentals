# FitCheck Rentals backlog

Check off with `[x]`. Build in phase order. Full rules live in `PROJECT.md`.

## Phase 0: Foundation

- [x] `docker-compose.yml` with healthcheck
- [x] `.env` DB vars set
- [x] Composer `dev` and `setup` scripts start Docker
- [x] Theme tokens, zero radius, three color layers
- [x] Fonts: Bebas Neue, Roboto, JetBrains Mono
- [x] Lucide square caps rule
- [x] `formatPeso()` helper
- [x] `RentalDays` count and late, with tests
- [x] App timezone `Asia/Manila`
- [x] `Model::shouldBeStrict()`
- [x] Wordmark and icon mark
- [x] CI on SQLite, formatter settings match editor

## Phase 1: Accounts and roles

- [ ] `Role` enum
- [ ] Migration: `role`, `phone`, `suspended_at` on users, `password` nullable
- [ ] `owner_profiles` migration and model
- [ ] `owner` and `admin` middleware
- [ ] Share `isOwner` and `isAdmin` through Inertia
- [ ] Block suspended users at login
- [ ] Owner profile setup page (requires phone)
- [ ] Navbar mode switch
- [ ] Seed an admin

## Phase 2: Social login

- [ ] Install Socialite, configure Google and Facebook in `.env.example`
- [ ] `SocialProvider` enum, `social_accounts` migration and model
- [ ] Redirect and callback routes
- [ ] Callback rules: log in, create, or refuse when email exists
- [ ] Connected accounts in Settings: link and unlink
- [ ] Block unlinking the last login method
- [ ] Set password for social only users
- [ ] Tests with a mocked Socialite user

## Phase 3: Categories

- [ ] `categories` migration, model, factory
- [ ] Admin categories CRUD pages
- [ ] Deactivate instead of delete when in use
- [ ] Seed the five default categories

## Phase 4: Items

- [ ] `ItemStatus`, `UnitCondition`, `UnitStatus` enums
- [ ] `items`, `item_images`, `item_units` migrations, models, factories
- [ ] Item policy and owner scoped queries
- [ ] Owner items list
- [ ] Item create and edit form
- [ ] Photo upload, reorder, delete
- [ ] Units manager with suggested labels
- [ ] Delete item only if never booked, else pause
- [ ] Retire units

## Phase 5: Marketplace

- [ ] Marketplace page, active items only, paginated
- [ ] Search, category, size, and price filters in the query string
- [ ] Item detail page with gallery

## Phase 6: Bookings

- [ ] `BookingStatus` enum with allowed transitions
- [ ] `bookings` migration, model, factory
- [ ] Availability check for a unit and date range
- [ ] Request booking with automatic unit assignment
- [ ] Phone prompt for renters without one
- [ ] Approve with recheck and unit swap
- [ ] Reject and cancel
- [ ] Mark active and mark returned
- [ ] Renter: my bookings and booking detail with days late
- [ ] Owner: booking requests page and dashboard

## Phase 7: Admin

- [ ] Admin users list, suspend and unsuspend
- [ ] Hide suspended owners' items from the marketplace
- [ ] Take down an item with a reason
- [ ] Owner resubmit flow to `pending_review`
- [ ] Admin review queue: approve or take down again

## Phase 8: Finish

- [ ] Seeders covering every status
- [ ] Landing page
- [ ] Em dash check passes
- [ ] `composer ci:check` passes
