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

- [x] `Role` enum
- [x] Migration: `role`, `phone`, `suspended_at` on users, `password` nullable
- [x] `owner_profiles` migration and model
- [x] `owner` and `admin` middleware
- [x] Share `isOwner` and `isAdmin` through Inertia
- [x] Block suspended users at login
- [x] Owner profile setup page (requires phone)
- [x] Navbar mode switch
- [x] Seed an admin

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

- [x] `categories` migration, model, factory
- [x] Admin categories CRUD pages
- [x] Deactivate instead of delete when in use
- [x] Seed the five default categories

## Phase 4: Items

- [x] `ItemStatus`, `UnitCondition`, `UnitStatus` enums
- [x] `items`, `item_images`, `item_units` migrations, models, factories
- [x] Item policy and owner scoped queries
- [x] Owner items list
- [x] Item create and edit form
- [x] Photo upload, reorder, delete
- [x] Units manager with suggested labels
- [x] Delete item only if never booked, else pause
- [x] Retire units

## Phase 5: Marketplace

- [x] Marketplace page, active items only, paginated
- [x] Search, category, size, and price filters in the query string
- [x] Item detail page with gallery

## Phase 6: Bookings

- [x] `BookingStatus` enum with allowed transitions
- [x] `bookings` migration, model, factory
- [x] Availability check for a unit and date range
- [x] Request booking with automatic unit assignment
- [x] Phone prompt for renters without one
- [x] Approve with recheck and unit swap
- [x] Reject and cancel
- [x] Mark active and mark returned
- [x] Renter: my bookings and booking detail with days late
- [x] Owner: booking requests page and dashboard

## Phase 7: Admin

- [ ] Admin users list, suspend and unsuspend
- [x] Hide suspended owners' items from the marketplace
- [ ] Take down an item with a reason
- [ ] Owner resubmit flow to `pending_review`
- [ ] Admin review queue: approve or take down again

## Phase 8: Finish

- [ ] Seeders covering every status
- [ ] Landing page
- [ ] Em dash check passes
- [ ] `composer ci:check` passes
