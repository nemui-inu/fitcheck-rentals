# Diagrams

GitHub renders these Mermaid blocks as diagrams. Rules behind them live in [`PROJECT.md`](../PROJECT.md).

## Entity relationships

Also available as a standalone file: [`erd.mmd`](erd.mmd).

```mermaid
erDiagram
  USERS ||--o{ SOCIAL_ACCOUNTS : "signs in with"
  USERS ||--o| OWNER_PROFILES : "opts into"
  USERS ||--o{ BOOKINGS : rents
  USERS ||--o{ ITEMS : moderates
  OWNER_PROFILES ||--o{ ITEMS : owns
  CATEGORIES ||--o{ ITEMS : groups
  ITEMS ||--o{ ITEM_IMAGES : has
  ITEMS ||--o{ ITEM_UNITS : "has copies"
  ITEM_UNITS ||--o{ BOOKINGS : "reserved by"

  USERS {
    bigint id PK
    string name
    string email
    string password "nullable"
    enum role "user or admin"
    string phone "nullable"
    timestamp suspended_at
  }
  SOCIAL_ACCOUNTS {
    bigint id PK
    bigint user_id FK
    enum provider "google or facebook"
    string provider_user_id
  }
  OWNER_PROFILES {
    bigint id PK
    bigint user_id FK
    string shop_name
    string meetup_area
  }
  CATEGORIES {
    bigint id PK
    string name
    string code "WIG"
    boolean is_active
  }
  ITEMS {
    bigint id PK
    bigint owner_profile_id FK
    bigint category_id FK
    string name
    string series
    string character
    string size
    text description
    int daily_rate
    int deposit
    enum status
    timestamp taken_down_at
    bigint taken_down_by FK
    text takedown_reason
  }
  ITEM_IMAGES {
    bigint id PK
    bigint item_id FK
    string path
    int sort_order
  }
  ITEM_UNITS {
    bigint id PK
    bigint item_id FK
    string label "WIG-001"
    enum condition
    enum status
  }
  BOOKINGS {
    bigint id PK
    string reference
    bigint item_unit_id FK
    bigint renter_id FK
    date start_date
    date end_date
    int total
    int deposit
    enum status
    timestamp returned_at
  }
```

## Areas by role

```mermaid
flowchart LR
  guest([Guest]) --> public[Landing, marketplace, item detail]
  user([User]) --> public
  user --> renter[My bookings]
  user --> settings[Settings, connected accounts]
  user -->|creates shop profile| owner([Owner])
  owner --> ownerArea["/owner: items, units, photos, booking requests"]
  admin([Admin]) --> adminArea["/admin: users, categories, item review"]
```

## Booking lifecycle

```mermaid
stateDiagram-v2
  [*] --> pending: renter requests
  pending --> approved: owner approves
  pending --> rejected: owner rejects
  pending --> cancelled: renter cancels
  approved --> active: owner marks pickup
  approved --> cancelled: renter or owner cancels
  active --> returned: owner marks return
  returned --> [*]
  rejected --> [*]
  cancelled --> [*]
```

## Requesting and approving a booking

```mermaid
sequenceDiagram
  actor Renter
  participant App
  participant DB
  actor Owner

  Renter->>App: Request item for Oct 12 to Oct 14
  App->>DB: Find an active unit free for those dates
  alt a unit is free
    App->>DB: Create booking, pending, with that unit
    App-->>Renter: Request sent
  else no unit free
    App-->>Renter: Those dates are fully booked
  end

  Owner->>App: Approve booking
  App->>DB: Recheck the unit inside a transaction
  alt unit still free
    App->>DB: Set status approved
  else unit taken, another free
    App->>DB: Swap unit, set status approved
  else no unit free
    App-->>Owner: No copies free for those dates
  end
```

## Social login

```mermaid
flowchart TD
  start([Callback from Google or Facebook]) --> linked{Provider account already linked?}
  linked -->|yes| suspended{User suspended?}
  linked -->|no| exists{Email already has an account?}
  exists -->|no| create[Create user with no password, link provider]
  create --> login([Log in])
  exists -->|yes| refuse([Refuse: log in with your password, then connect in Settings])
  suspended -->|no| login
  suspended -->|yes| blocked([Refuse: account suspended])
```

## Item moderation

```mermaid
stateDiagram-v2
  [*] --> draft: owner creates
  draft --> active: owner publishes
  active --> paused: owner pauses
  paused --> active: owner resumes
  active --> taken_down: admin takes down with reason
  taken_down --> pending_review: owner edits and resubmits
  pending_review --> active: admin approves
  pending_review --> taken_down: admin rejects again
```
