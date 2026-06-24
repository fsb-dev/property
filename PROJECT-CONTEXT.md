# Project Context & Scope — LakeView Residences

> **Read this first, fully, before generating any code, schema, or plan.**
> This document defines WHAT we are building now and, just as importantly, WHAT we are NOT building yet.
> If a request seems to require something listed under "Out of scope / later," STOP and flag it — do not build it.

---

## 0. One-line summary

A web-based **real-estate property portal** for an apartment developer in Bangladesh. It has **two portals for now — an Admin portal and a Client (buyer) portal** — sharing one Laravel backend and one database. It is designed so it can become a multi-tenant **SaaS later**, but **the SaaS is NOT being built now.**

---

## 1. What the software is

A real-estate company (the developer/seller) builds apartment projects and sells flats to buyers. This software gives:

- **The company (admins)** a back-office to manage projects, flats, buyers, bookings, payment plans, installments, payments, construction progress, and documents.
- **The buyers (clients)** a self-service portal to see the flat(s) they bought/reserved, their price and balance, installment schedule, payment history, construction progress, documents, mortgage/investment calculators, and an AI helper.

The screenshots provided are the **client portal** (Dashboard, My Properties, Payments & Installments, Mortgage & Financing, Investment Analyzer, Construction Progress, Community & Future, Documents, AI Property Advisor, Support & Help). The **admin portal** is the back-office that produces all the data those screens display.

**Purpose right now:** a working **demo** for a CEO presentation, built in ~6 weeks. Real calculations, simulated payments.

---

## 2. Business domain (how the real estate business works here)

Bangladesh apartment buying lifecycle: **booking → installments → handover → registration.**

- A buyer **reserves** a flat with booking money, pays a **down payment** (typically 20–25% of the flat price), then clears the balance through a **construction-linked payment plan** — installments tied to construction milestones (foundation, structure, brickwork, finishing, handover).
- The portal shows each buyer their flat's price, paid vs outstanding, next installment + countdown, and live construction progress.

---

## 3. The portals — NOW vs LATER

### Build now (two portals)
1. **Admin portal** — used by the real-estate company's staff. Manages everything.
2. **Client portal** — used by buyers. Read-mostly view of their own flats + payments.

### Introduce later (DO NOT build now)
3. **SaaS Owner portal** — used by the company that *sells this software* to multiple real-estate companies.

**Hierarchy (for the future SaaS):**
```
SaaS Owner (sells the software)
   └── Real-estate company = "tenant" = the Admin   (buys/uses the software)
          └── Clients = buyers                        (buy flats from that company)
```

For now there is **exactly one** real-estate company (one tenant). The SaaS owner layer does not exist yet.

---

## 4. Who does what

**Admin (company staff) can:**
manage projects & units (flats); create buyer accounts; create bookings (assign a flat to a buyer, reserved or purchased); build payment plans and generate installment schedules; **record payments**; update construction milestones & post site photos; upload documents (metadata); maintain investment/market data, nearby developments, and bank partners.

**Client (buyer) can:**
log into their own portal; see their dashboard; view each flat they own/reserved with price, paid/outstanding, next installment; view installment schedule & payment history; **simulate a payment** ("Pay Now"); track construction progress; view/download documents; use mortgage & investment calculators; chat with the AI advisor.

---

## 5. The core ownership model (get this right)

```
clients  1 ───*  bookings  *─── 1  units  *─── 1  projects
```

- A **client** can own/reserve **one or many flats** — in the **same project or different projects**.
- This is handled by the **bookings** table: one client has many bookings; **each booking is one flat.**
- Each booking carries **its own** agreed price, booking/handover dates, payment plan, installments, payments, and construction-progress view.

Do not assume "one client = one flat." A client may hold several.

---

## 6. Tech stack (decided — do not change)

- **Backend:** Laravel 13
- **Frontend:** Inertia.js + Vue 3 + Tailwind
- **Database:** MySQL (single database)
- **Auth:** session-based, **two guards** — `web` for admins (`users` table), `client` for buyers (`clients` table)
- **Desktop delivery:** ship as a **PWA** (installable, runs in its own window). NativePHP is a *later* option, **not now.**

---

## 7. Multi-tenancy (present in schema, dormant in behavior)

- Every business table has a **`tenant_id`** column. There is **one tenant row** now.
- A single resolver returns tenant id = 1 for the whole app. That is the only place tenant identity is decided.
- This is the *only* SaaS-related thing built now. It costs one column per table and makes the future SaaS an **additive** change (no rebuild).

**Going SaaS later means (DO NOT do now):** change the resolver to read tenant from subdomain/login, add the SaaS owner portal, tenant onboarding/signup, subscription billing, custom domains.

Naming rule: the column is **`tenant_id`**, the table is **`tenants`**. Never use `company_id`.

---

## 8. Real vs simulated

- **REAL (must be correct):** all financial math — outstanding balance, paid %, installment schedule generation, next-due & countdown, EMI/amortization (mortgage), rental yield & ROI (investment), construction progress roll-up.
- **SIMULATED (fake):** the payment gateway. "Pay Now" / "Record payment" writes a completed payment and advances installment status; no real bKash/Nagad/card call. A real gateway can be slotted in later behind one method, no schema change.

---

## 9. IN SCOPE NOW (build this)

- Admin portal + Client portal (two separate portals, one backend)
- `clients` table + `users` table, two auth guards
- Projects, units (flats), bookings (client owns flat), payment plans, installments, payments
- Real calculation services (schedule, balances, EMI, yield/ROI, progress)
- Construction milestones + site updates
- Documents (metadata only)
- My Properties (purchased/reserved/favorites/compared)
- Mortgage & Investment calculators (real math)
- Community & Future (seed data)
- `tenant_id` on every table (one tenant, dormant)
- Seed/demo data so screens show real numbers
- PWA wrap; PDF statement
- (Good-to-have, last) AI advisor with a basic knowledge base

---

## 10. OUT OF SCOPE / LATER — DO NOT BUILD NOW  ⛔

These are the guardrails. If a task implies any of these, flag it and stop — do not start building it.

- ⛔ **The SaaS itself** — no SaaS owner portal, no multi-company onboarding, no tenant signup flow.
- ⛔ **Subscription / billing** for real-estate companies paying the SaaS owner (no Stripe/Cashier, no plans/pricing).
- ⛔ **Subdomain / multi-domain tenant routing** (`acme.app.com`). Resolver stays hardcoded to tenant 1.
- ⛔ **NativePHP / native desktop build.** PWA only for now.
- ⛔ **Real payment gateway integration** (bKash/Nagad/card). Payments are simulated.
- ⛔ **Real document file storage** (S3/disk binding). Documents are metadata only.
- ⛔ **Support & Help ticketing system.** Static page for the demo.
- ⛔ **Separate mobile app / public REST API for third parties.** Inertia only.
- ⛔ Do **not** turn `tenant_id` into a live multi-tenant system, add a `domains` table, or build tenant switching.
- ⛔ Do **not** invent extra modules not shown in the screenshots or listed in "In scope now."

If unsure whether something is in scope: **ask first, build second.**

---

## 11. Deferred details (fine to stub for the demo)

- Notifications: use Laravel's built-in table if needed, or a seeded count for the bell badge.
- AI advisor: start with deterministic answers to the quick-question chips (real numbers from the calculation service); the free-text LLM path is added last and is optional.

---

## 12. Timeline

~6 weeks total. Weeks 1–2 setup + schema + calculation services; weeks 3–4 client portal; week 5 admin portal + record-payment loop; week 6 polish, PDF, PWA, demo data, dry run.

---

## 13. The one-sentence rule to remember

**Build a single-company app with two portals (admin + client) where the schema already carries `tenant_id` — and do not build any SaaS feature until explicitly asked in a later phase.**
