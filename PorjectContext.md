#  Plant Disease Diagnosis System

> A web application that helps farmers and plant enthusiasts identify plant diseases through image uploads, powered by AI. Built with **Laravel 12 + Inertia.js + Vue 3**.

---

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [User Roles](#user-roles)
- [Features](#features)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Routing Map](#routing-map)
- [Authentication & Middleware](#authentication--middleware)
- [Getting Started](#getting-started)
- [Seeded Accounts](#seeded-accounts)
- [Environment Variables](#environment-variables)
- [Roadmap](#roadmap)

---

## Overview

PlantDoc allows users to **upload a photo of a plant**, and the system diagnoses potential diseases using AI (Gemini Vision API). Upload requests return quickly, and diagnosis processing runs in a background queue with live status polling.

There are **two distinct portals**:

| Portal | URL Prefix | Who |
|--------|-----------|-----|
| User Portal | `/client/*` | Regular users who upload and view diagnoses |
| Admin Portal | `/admin/*` | Admins who manage users, diseases, and all diagnoses |

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 |
| Frontend | Vue 3 (Composition API) |
| SPA Bridge | Inertia.js |
| Styling | Tailwind CSS |
| Auth | Custom RBAC (no Breeze) |
| Database | MySQL |
| AI Diagnosis | Google Gemini generateContent API |
| Route Helpers | Ziggy |

---

## User Roles

### Regular User
A registered member who uses the app to diagnose their plants.

**Can:**
- Register and log in
- Upload a plant photo and receive an AI-powered diagnosis
- View their own diagnosis history
- See detailed results: disease name, confidence %, symptoms, and treatment

**Cannot:**
- Access the admin panel
- View other users' diagnoses
- Manage the disease knowledge base

---

###  Admin
A privileged account that manages the platform and its users.

**Can:**
- Access a separate Admin Dashboard with platform-wide statistics
- **Manage Users** — view, edit, activate/deactivate, or delete user accounts
- **Manage Diseases** — full CRUD on the disease knowledge base (name, symptoms, treatment, plant type, images)
- **View All Diagnoses** — browse every diagnosis submitted across all users
- View diagnosis details including the uploaded image and AI response

**Cannot:**
- Submit their own plant diagnoses (admin accounts are for management only)

---

## Features

### User Portal (`/client`)

| Page | Route | Description |
|------|-------|-------------|
| Dashboard | `/client/dashboard` | Personal stats: total diagnoses, recent activity |
| Diagnose | `/client/diagnose` | Upload plant photo → queue analysis and track status |
| History | `/client/history` | List of all past diagnoses |
| Result | `/client/result/{id}` | Detailed diagnosis result page |

### Admin Portal (`/admin`)

| Page | Route | Description |
|------|-------|-------------|
| Dashboard | `/admin/dashboard` | Platform stats: total users, diagnoses this month, most common diseases |
| Users | `/admin/users` | List, view, edit, activate/deactivate, delete users |
| User Detail | `/admin/users/{id}` | View a specific user's profile and their diagnosis history |
| Diseases | `/admin/diseases` | CRUD — manage the disease knowledge base |
| All Diagnoses | `/admin/diagnoses` | Browse all diagnoses across all users |
| Diagnosis Detail | `/admin/diagnoses/{id}` | View full diagnosis record with uploaded image |

---

## Project Structure

```
plantdoc/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php         # Login, logout, role-based redirect
│   │   │   │   └── RegisterController.php      # Register (always role: user)
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php     # Platform stats
│   │   │   │   ├── UserController.php          # Manage users
│   │   │   │   ├── DiseaseController.php       # CRUD diseases
│   │   │   │   └── DiagnosisController.php     # View all diagnoses
│   │   │   └── User/
│   │   │       ├── DashboardController.php     # Personal stats
│   │   │       └── DiagnosisController.php     # Upload + view own diagnoses
│   │   └── Middleware/
│   │       ├── HandleInertiaRequests.php       # Shares auth.user to all pages
│   │       ├── RoleMiddleware.php              # role:admin / role:user
│   │       ├── AdminMiddleware.php             # Hard admin-only guard
│   │       └── EnsureUserIsActive.php          # Auto-logout deactivated users
│   ├── Models/
│   │   ├── User.php                            # role, is_active, isAdmin()
│   │   ├── Diagnosis.php                       # user_id, image, result
│   │   └── Disease.php                         # Knowledge base
│   └── Services/
│       └── DiagnosisService.php                # OpenAI Vision integration
│
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php              # + role + is_active columns
│   │   ├── create_diagnoses_table.php
│   │   └── create_diseases_table.php
│   └── seeders/
│       └── DatabaseSeeder.php                  # Seeds admin + demo user
│
├── routes/
│   ├── web.php                                 # All app routes
│   └── auth.php                                # Login / register / logout routes
│
└── resources/
    └── js/
        ├── app.js                              # Inertia + Vue 3 bootstrap
        ├── Pages/
        │   ├── Auth/
        │   │   ├── Login.vue
        │   │   └── Register.vue
        │   ├── Admin/
        │   │   ├── Dashboard.vue               # Stats, charts, recent activity
        │   │   ├── Users/
        │   │   │   ├── Index.vue               # User list with filters
        │   │   │   ├── Show.vue                # User profile + history
        │   │   │   └── Edit.vue                # Edit user details
        │   │   ├── Diseases/
        │   │   │   ├── Index.vue               # Disease list
        │   │   │   ├── Create.vue
        │   │   │   └── Edit.vue
        │   │   └── Diagnoses/
        │   │       ├── Index.vue               # All diagnoses
        │   │       └── Show.vue                # Full diagnosis detail
        │   └── User/
        │       ├── Dashboard.vue               # Personal stats
        │       ├── Diagnose.vue                # Upload form
        │       ├── History.vue                 # Past diagnoses
        │       └── Result.vue                  # Diagnosis result
        ├── Layouts/
        │   ├── AdminLayout.vue                 # Sidebar nav for admin
        │   └── UserLayout.vue                  # navbar nav for user
        └── composables/
            └── useAuth.js                      # user, isAdmin, isUser refs
```

---

## Database Schema

## Privacy & retention

### Activity log PII (IP address, user agent)

The `activities` table includes `ip_address` and `user_agent` for security/auditing.

- **Lawful basis (GDPR):** legitimate interests (security/audit trail) and, where applicable, compliance with legal obligations.
- **Retention:** these fields are retained for a configurable period and then **anonymized (set to `null`)** or **deleted**.
- **Configuration:** `ACTIVITY_PII_RETENTION_DAYS` (default `30`) and `ACTIVITY_PII_RETENTION_ACTION` (`anonymize` or `delete`).
- **Enforcement:** scheduled command `activities:apply-pii-retention` (see `routes/console.php`).

### `users`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | string | hashed |
| role | enum | `admin` \| `user` |
| is_active | boolean | default `true` |
| email_verified_at | timestamp | nullable |
| remember_token | string | |
| timestamps | | |

### `diagnoses`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK → users | |
| image_path | string | stored in `/storage/diagnoses` |
| plant_name | string | nullable (user input or AI detected) |
| disease_name | string | from AI |
| confidence_score | decimal | 0.00–100.00 |
| symptoms | text | from AI |
| treatment | text | from AI |
| status | string | `pending` \| `processing` \| `completed` \| `failed` |
| failure_reason | text | nullable, populated when AI processing fails |
| attempted_at | timestamp | nullable, latest queue attempt time |
| completed_at | timestamp | nullable, successful completion time |
| raw_ai_response | json | full API response |
| timestamps | | |

### `diseases`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | e.g. "Powdery Mildew" |
| plant_type | string | e.g. "Tomato", "Rose" |
| symptoms | text | |
| treatment | text | |
| prevention | text | |
| image_path | string | nullable |
| timestamps | | |

---

## Routing Map

```
GET  /                          → redirects based on role

# Auth (custom — no Breeze)
GET  /login                     → Auth/Login.vue        [guest]
POST /login                     → authenticate + role redirect
GET  /register                  → Auth/Register.vue     [guest]
POST /register                  → create user (role: user)
POST /logout                    → logout                [auth]

# User Routes  [middleware: auth, client]
GET  /client/dashboard          → ClientDashboardController@index
GET  /client/diagnose           → DiagnoseController@index
POST /client/diagnose           → DiagnoseController@store → create pending + dispatch queue job
GET  /client/diagnose/{id}/status → DiagnoseController@status → polling endpoint
GET  /client/reports            → ReportController@index
GET  /client/support            → SupportController@index
GET  /client/knowledgehub       → KnowledgeHubController@index
GET  /client/profile/{user}     → ProfileController@show

# Admin Routes  [middleware: auth, role:admin]
GET  /admin/dashboard           → Admin/Dashboard.vue
GET  /admin/users               → Admin/Users/Index.vue
GET  /admin/users/{id}          → Admin/Users/Show.vue
GET  /admin/users/{id}/edit     → Admin/Users/Edit.vue
PATCH /admin/users/{id}         → update user
PATCH /admin/users/{id}/toggle-active → activate/deactivate
DELETE /admin/users/{id}        → delete user

GET  /admin/diseases            → Admin/Diseases/Index.vue
GET  /admin/diseases/create     → Admin/Diseases/Create.vue
POST /admin/diseases            → store
GET  /admin/diseases/{id}/edit  → Admin/Diseases/Edit.vue
PUT  /admin/diseases/{id}       → update
DELETE /admin/diseases/{id}     → delete

GET  /admin/diagnoses           → Admin/Diagnoses/Index.vue
GET  /admin/diagnoses/{id}      → Admin/Diagnoses/Show.vue
```

---

## Async Diagnosis Pipeline

The diagnosis workflow is asynchronous for scale:

1. Client uploads image to `/client/diagnose`.
2. Backend validates input and quota, stores image, and creates a diagnosis record with `status = pending`.
3. `ProcessDiagnosisJob` is dispatched to the queue.
4. Job moves status to `processing`, requests Gemini analysis via `DiagnoseService`, then writes final fields and sets `status = completed`.
5. On failure, job sets `status = failed` with `failure_reason`.
6. Frontend polls `/client/diagnose/{id}/status` until status is final.

### Queue Worker

Run a worker so queued diagnoses are processed:

```bash
php artisan queue:work
```

Set `QUEUE_CONNECTION` in `.env` (default is `database` in this project).

## Authentication & Middleware

Auth is handled entirely by two plain controllers — **no Laravel Breeze, no starter kits required**.

### Controllers

| Controller | Responsibility |
|-----------|---------------|
| `Auth\LoginController` | Show login form, authenticate, redirect by role, logout |
| `Auth\RegisterController` | Show register form, create user with `role: user`, auto-login |

After login, users are redirected automatically:
- **Admin** → `/admin/dashboard`
- **User** → `/client/dashboard`

### Middleware Stack

| Middleware | Alias | Purpose |
|-----------|-------|---------|
| `EnsureAdmin` | `admin` | Restricts admin routes |
| `EnsureClient` | `client` | Restricts client routes |
| `HandleInertiaRequests` | *(global)* | Shares `auth.user`, `flash`, Ziggy to all Vue pages |

### Using roles in Vue components

```js
import { useAuth } from '@/composables/useAuth'

const { user, isAdmin, isUser } = useAuth()
```

```html
<AdminLayout v-if="isAdmin" />
<UserLayout v-else />
```

### Accessing auth in any Vue page

The `auth.user` object is automatically available in every page via Inertia's shared props:

```js
import { usePage } from '@inertiajs/vue3'

const { auth } = usePage().props
// auth.user = { id, name, email, role, is_active }
```

---


```


## Roadmap

### Phase 1 — Auth & Foundation 
- [x] User model with `role` and `is_active`
- [x] Custom `LoginController` and `RegisterController` (no Breeze)
- [x] Role middleware (`role:admin`, `role:user`)
- [x] `EnsureUserIsActive` global middleware
- [x] Inertia shared `auth.user` props
- [x] Login & Register pages (Vue)
- [x] Separated route groups for admin and user

### Phase 2 — Core Features 
- [ ] Admin Dashboard with platform stats
- [ ] User Dashboard with personal stats
- [ ] Plant image upload + DiagnosisService (OpenAI Vision)
- [ ] Diagnosis result page
- [ ] User diagnosis history

### Phase 3 — Admin Management 
- [ ] User management (list, view, edit, activate/deactivate, delete)
- [ ] Disease knowledge base CRUD
- [ ] All diagnoses viewer with filters

### Phase 4 — Polish
- [ ] Confidence score badge (color-coded)
- [ ] Export diagnosis as PDF
- [ ] Admin analytics charts (diagnoses per month, top diseases)
- [ ] Email notifications on new diagnosis
- [ ] Mobile-responsive layouts

---

> Built with  using Laravel 12 + Inertia.js + Vue 3
