# Property App — Task Board

> Stack: Laravel 13 · Vue 3 · Inertia.js · Tailwind CSS · Spatie Media Library  
> DB: Docker → `docker exec property_app php artisan ...`  
> Deploy strategy: `migrate:fresh --seed` (edit original migrations, no additive files)

---

## ✅ Done

- [x] Admin auth + role/permission system (Spatie)
- [x] Project module — CRUD, enums (ProjectType, ProjectCategory, ProjectStatus), Spatie media, seeder (7 projects)
- [x] Unit module — CRUD, UnitType enum (13 types) + `formConfig()` for type-aware form, seeder (155 units)
- [x] Client/Customer module — CRUD, enums (ClientStatus, ClientSource), Spatie media (avatar + KYC docs), seeder (15 clients)
- [x] Sidebar redesign — dark navy gradient, prototype accent color #5B3DF5
- [x] Design system — Inter font, CSS token system (RGB portal vars + HSL shadcn vars), card shadows, page bg #F8F9FC
- [x] Projects Index page — 5 KPI cards, status tabs, prototype table style, status donut, top performing + recent activities sidebar widgets

---

## 🔴 Next — Database Restructure

> All changes go into the **original migrations** (not new additive files). Run `migrate:fresh --seed` after.

### 1. Modify `projects` table migration
**File:** `database/migrations/2026_01_01_000004_create_projects_table.php`  
Also collapse the 2 additive migrations into it:
- `2026_06_25_000001_add_specifications_to_projects_table.php` → merge then delete
- `2026_06_25_000002_add_facilities_to_projects_table.php` → merge then delete (facilities moves to pivot)

**Remove columns:**
- `cover_image` (string) — Spatie `cover` collection already handles this
- `facilities` (JSON) — moving to `project_facility` pivot table

**Add columns:**
```
project_code        string nullable unique          — auto "LVR-2026"
theme_color         string nullable                 — hex, e.g. "#818CF8" for card avatar color
developer_id        unsignedBigInteger nullable FK → developers
developer_name      string nullable                 — fallback for one-off developer names
start_date          date nullable
land_area           decimal(10,2) nullable
land_area_unit      string nullable                 — sqft | katha | acres | marla
built_up_area       decimal(10,2) nullable
estimated_value     bigint nullable                 — BDT, no decimals
booking_amount      decimal(12,2) nullable
booking_amount_type string nullable                 — fixed | percentage
commission_pct      decimal(5,2) nullable
payment_plan_months int nullable
published_at        timestamp nullable
```

**Keep unchanged:** slug, name, status, description, location, address, latitude, longitude,
handover_date, overall_progress, total_floors, total_units, specifications (JSON), type, category, tenant_id

**Add `Draft` to `ProjectStatus` enum** — default status for new projects.  
Publish action transitions: draft → planning | under_construction | completed

---

### 2. Create `project_blocks` table (new migration file)
**Purpose:** Replaces the loose `block` string on units. Each block is a named physical section of
a project (Tower A, Commercial Podium, Villa Cluster). A project can have 1 to many blocks.
Each block has ONE type. Mixed-floor buildings are handled by adding multiple blocks
(e.g. "Ground Floor Commercial" + "Residential Tower").

```
project_blocks
  id
  project_id      FK → projects (cascade delete)
  name            — "Tower A", "Ground Podium", "Villa Cluster A"
  type            — uses UnitType enum values: apartment | shop | office | villa | warehouse …
  floor_start     int nullable    — physical floor where this block begins (e.g. 1)
  floor_end       int nullable    — physical floor where this block ends (e.g. 20)
  planned_units   int nullable    — rough target; compared to actual unit count for progress
  sort_order      int default 0
  timestamps
```

---

### 3. Modify `units` table — add block FK + sort order
**File:** `database/migrations/2026_01_01_000005_create_units_table.php`

**Add columns:**
```
block_id      unsignedBigInteger nullable   FK → project_blocks (nullOnDelete)
sort_order    unsignedSmallInteger default 0   — circle order within a floor row in blueprint UI
```

**Keep:** project_id (useful for direct queries), block (string — keep as fallback until all
units have block_id, then drop in a later pass), floor, unit_number, type, bedrooms,
size_sqft, view, price, status, handover_date, tenant_id

**Add index:** `(block_id, floor, sort_order)` — powers the blueprint canvas query

---

### 4. Create `developers` table (new)
```
developers
  id
  tenant_id       FK nullable
  name
  phone           nullable
  email           nullable
  address         nullable
  website         nullable
  timestamps
  + Spatie media: logo (single file)
```

---

### 5. Create `facilities` + `project_facility` tables (new)
**Purpose:** Replaces `facilities` JSON on projects. Allows filtering "all projects with a gym",
client portal amenity search, and per-facility icons.

```
facilities
  id
  name            unique   — "Gym", "Swimming Pool", "24/7 Security"
  group           string   — Recreational | Security | Utilities | Transport | Health
  icon            string nullable

project_facility  (pivot)
  project_id      FK
  facility_id     FK
  PRIMARY KEY (project_id, facility_id)
```

**Seed `facilities` master list** from the existing facility chip data in `ProjectCategory` config.

---

### 6. Create `project_compliances` table (new)
**Purpose:** Tracks statutory approvals and certifications per project. Replaces nothing
(currently not in schema at all). Needed for the Compliance tab in the new project form.

```
project_compliances
  id
  project_id      FK → projects (cascade delete)
  name            — "Building Permit", "Fire Safety NOC", "LEED Certification"
  type            — approval | certification
  status          — pending | obtained | not_required
  obtained_date   date nullable
  expiry_date     date nullable
  notes           text nullable
  timestamps
```

---

### 7. Update seeders after migration changes
- `ProjectSeeder` — add `project_code`, `theme_color`, `developer_id` or `developer_name`,
  remove facilities JSON (seed via pivot), create `project_blocks` rows for each project
- `DatabaseSeeder` — add `DeveloperSeeder` and `FacilitySeeder` before `ProjectSeeder`
- Update unit seeder rows to set `block_id` where applicable

---

## 🟡 Project Form Rewrite

> **Philosophy:** Simple 7-tab form, zero type-adaptive conditional logic.
> Project type is just a display label, not a form driver.
> Complexity lives in the Blueprint UI (Unit Inventory module), not here.

### Tab structure (new)

| Tab | Fields | Notes |
|-----|--------|-------|
| 1 · Identity | name, project_code (auto+editable), theme_color (6 swatches), status, start_date, handover_date, description | `project_code` auto-generates as name initials + year on blur |
| 2 · Location | location, address, latitude, longitude | Same as current |
| 3 · Buildings | Blocks repeater — name, type dropdown, floor_start, floor_end, planned_units | Inline table rows, live summary below |
| 4 · Developer & Financials | developer (searchable select or type new), land_area + unit, built_up_area, estimated_value, booking_amount + type toggle, commission_pct, payment_plan_months | |
| 5 · Facilities | Chip groups (Recreational, Security, Utilities, Transport) | Now writes to `project_facility` pivot |
| 6 · Compliance | Inline editable list — name, type, status, obtained_date | Writes to `project_compliances` |
| 7 · Media & Docs | Cover + gallery + documents | Existing Spatie components, no change |

**Footer (every tab):** `[Save Draft]` left · `[← Back]` `[Next →]` right  
**Last tab footer:** `[Save Draft]` left · `[← Back]` `[Publish Project]` right  
**Header:** Shows project type badge (if set) + "Draft saved" green dot indicator

**Files to change:**
- `resources/js/Pages/Admin/Projects/partials/ProjectForm.vue` — full rewrite
- `resources/js/Pages/Admin/Projects/Create.vue` — add `blocks[]` to useForm
- `resources/js/Pages/Admin/Projects/Edit.vue` — load existing blocks as prop
- `app/Http/Requests/Admin/StoreProjectRequest.php` — add new field validation
- `app/Http/Requests/Admin/UpdateProjectRequest.php` — same
- `app/Services/ProjectService.php` — handle blocks CRUD on create/update, pivot sync for facilities, compliance rows
- `app/Http/Controllers/Admin/ProjectController.php` — pass blocks + facilities + compliances as props

---

## 🟡 Blueprint UI — Unit Inventory Visual Builder

> Separate page, not inside the project form.  
> Entry point: Project detail page → per-block "Build Inventory →" button.

### Concept
```
Block: Tower A  [Apartment]  Floors 3–20  [+ Add Floor]  [Auto-fill]

Floor 20  ● ●                     [+]
Floor 19  ● ● ●                   [+]
Floor 18  ● ● ● ●                 [+]
...
Floor 3   ● ● ●                   [+]

          ↑ click any ● → right slide-over panel with unit config
          Hover ● → shows × to delete
          Drag ● → reorders within floor (updates sort_order)

Summary: 18 floors · 64 units configured · 0 sold · 64 available
```

### Unit side panel (slides in on circle click)
Fields: unit_number (auto, editable), type (UnitType dropdown), floor (read-only),
size_sqft, price, bedrooms (conditional on type), view, status, handover_date, notes  
Actions: `[Delete Unit]` · `[Save]`

### Auto-fill dialog
```
Apply to:  ○ All floors  ○ Selected floors [floor picker]  ○ Selected units
Units per floor:   [4]
Type:              [2 Bed ▼]
Size (sqft):       [1150]
Price (BDT):       [8,500,000]
Bedrooms:          [2]
                              [Apply]
```
Backend: `POST /projects/{project}/blocks/{block}/units/bulk`  
Generates unit numbers as `{block_prefix}-{floor}{index}` e.g. A-301, A-302

### Bulk update (apply to selected circles)
Select multiple circles (checkbox mode) → toolbar appears:  
`[Change Type ▼]  [Set Price]  [Set Status ▼]  [Delete selected]`  
Backend: `POST /units/bulk-update` with `{ unit_ids[], fields{} }`

### Files to create/modify
- `resources/js/Pages/Admin/Units/Blueprint.vue` — new page (the canvas)
- `resources/js/Pages/Admin/Units/partials/BlueprintCanvas.vue` — floor rows + circles
- `resources/js/Pages/Admin/Units/partials/UnitSlideOver.vue` — right panel
- `resources/js/Pages/Admin/Units/partials/AutoFillDialog.vue` — bulk create modal
- `app/Http/Controllers/Admin/UnitController.php` — add `blueprint()`, `bulkCreate()`, `bulkUpdate()` methods
- `app/Services/UnitService.php` — add `bulkCreate()`, `bulkUpdate()`, `forBlueprint()` methods
- `routes/admin.php` — add blueprint route + bulk routes

---

## 🔵 Upcoming Modules (in build order)

### Dashboard v1
- Stats cards (total projects, units, clients, revenue)
- ApexCharts: units-by-status donut, projects-by-type bar
- Recent projects table (last 5)
- Recent clients list
- All data from existing tables — no new schema needed

### Bookings / Reservations module
- `bookings` table: client_id, unit_id, agent_id, amount, status, booking_date, notes
- Status flow: Draft → Confirmed → Agreement Signed → Sold / Cancelled
- Triggers unit.status → reserved | sold

### Payments & Installments module
- `payment_plans` table: booking_id, total_amount, installments JSON or rows
- `payments` table: booking_id, amount, due_date, paid_date, method, reference, receipt
- Depends on Bookings module

### Agents / Employees module
- `agents` table (or extend users): name, phone, email, commission_pct, targets
- Linked to bookings for commission calculation

### Reports module
- PDF/Excel export of projects, units, clients, payments
- No new schema — reads from existing tables

---

## 🔧 Housekeeping

- [ ] Remove the 2 additive project migrations after merging into original
- [ ] Drop `cover_image` string column from projects (use Spatie only)
- [ ] Drop `facilities` JSON column from projects (after pivot is live)
- [ ] Drop `block` string column from units (after `block_id` FK is fully used)
- [ ] Rename `floor` column on units to `floor_number` for clarity (optional, non-breaking)
- [ ] Wire `tenant_id` FK constraints once multi-tenancy is ready (currently nullable, no FK)
- [ ] Add `block_id` to unit seeder rows once `project_blocks` table exists

---

## 📐 DB Schema Quick Reference (target state)

```
projects
  id, tenant_id, developer_id FK, name, project_code, slug, theme_color,
  status, type, category, description, location, address, latitude, longitude,
  start_date, handover_date, land_area, land_area_unit, built_up_area,
  estimated_value, booking_amount, booking_amount_type, commission_pct,
  payment_plan_months, total_floors*, total_units*, overall_progress,
  specifications JSON, published_at, timestamps
  (* derived from blocks but kept as denormalised summary)

project_blocks
  id, project_id FK, name, type, floor_start, floor_end, planned_units, sort_order, timestamps

developers
  id, tenant_id FK, name, phone, email, address, website, timestamps + Spatie logo

facilities
  id, name (unique), group, icon

project_facility (pivot)
  project_id FK, facility_id FK

project_compliances
  id, project_id FK, name, type, status, obtained_date, expiry_date, notes, timestamps

project_market_stats (exists)
  id, tenant_id FK, project_id FK, price_per_sqft, avg_rental_income,
  annual_expenses, best/expected/conservative return, price_history JSON, timestamps

units
  id, tenant_id, project_id FK, block_id FK, unit_number, floor, sort_order,
  type, bedrooms, size_sqft, view, price, status, handover_date, timestamps
  + Spatie: documents

clients (exists)
  id, tenant_id, name, email, phone, gender, dob, nationality, nid, passport_no,
  occupation, address, source, status, notes, password, remember_token, timestamps
  + Spatie: avatar, kyc_documents
```
