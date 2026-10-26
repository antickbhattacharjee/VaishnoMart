# VaishnoMart 🛒
### Multi-Vendor Hyperlocal E-Commerce & On-Demand Delivery Platform
*Your Needs. Your Neighbourhood. Delivered.*

---

## 1. Project Overview

**VaishnoMart** is a complete, production-oriented, multi-vendor hyperlocal marketplace built on **Laravel 11** and **MySQL / SQLite**. VaishnoMart connects customers directly with verified local neighbourhood vendors (drinking water suppliers, cloud kitchens, dry fruit merchants, grocery stores, organic dairies, bakeries, and more) and coordinates on-demand delivery through a dedicated fleet of delivery riders.

The platform eliminates unnecessary intermediaries and automates:
- **Location-Aware Order Routing:** Identifies the nearest eligible vendor within serviceable radius using the **Haversine formula**.
- **Single-Vendor-Per-Order Model:** Ensures each order is sourced and dispatched from a single local store.
- **Atomic Concurrency & Stock Safety:** Row-level database locking prevents overselling during high-volume spikes.
- **Dual OTP Verification:** Enforces a strict two-stage cryptographic OTP verification (Pickup OTP verified before store departure; Customer Delivery OTP verified before final delivery confirmation).
- **Immutable Financial Ledgers:** Explicit transaction ledgers (`vendor_transactions` and `delivery_agent_earnings`) guarantee financial transparency and auditability.

---

## 2. Technology Stack

- **Backend Framework:** PHP 8.3+, Laravel 11.57+ (MVC Architecture)
- **API Authentication:** Laravel Sanctum
- **Database:** SQLite (default for instant zero-dependency local development) & MySQL 8.0+ ready
- **Frontend / Templating:** Blade Templating, HTML5, CSS3, JavaScript
- **CSS Framework & Icons:** Bootstrap 5.3.3 & Bootstrap Icons 1.11.3
- **Geographic Calculations:** Mathematical Haversine Great-Circle Distance Algorithm
- **Cryptographic Security:** Bcrypt-hashed OTP tokens with expiration and single-use invalidation
- **Testing Suite:** PHPUnit / Pest with 100% pass rate across 20 feature and unit tests (92 assertions)

---

## 3. Core Architecture & Lifecycle

### 9-Stage Strict Order State Machine
Every order transition is validated and enforced server-side. Direct status tampering is prevented.

```
[1. pending_vendor] 
       │ (Vendor Acceptance Window: 5 minutes)
       ├───► [Rejected / Expired] ───► Auto-Reassigned to Next Nearest Vendor
       ▼
[2. accepted] 
       │ (Generates Cryptographic Pickup OTP for Store)
       ▼
[3. preparing] 
       │ (Kitchen / Store Packing)
       ▼
[4. ready_for_pickup] 
       │ (Broadcasts Delivery Requests to Nearby Riders)
       ▼
[5. delivery_assigned] 
       │ (Rider Accepts Request)
       ▼
[6. picked_up / out_for_delivery] 
       │ (Rider Enters Store Pickup OTP -> Backend Validates)
       │ (Generates Customer Delivery OTP)
       ▼
[7. delivered] 
       │ (Rider Enters Customer Delivery OTP -> Backend Validates)
       ▼
[8. Financial Settlement & Ledger Credit]
       - Vendor Wallet Credited (`vendor_transactions`)
       - Delivery Agent Fee Credited (`delivery_agent_earnings`)
       - Platform Commission Logged
```

---

## 4. Default Demo Accounts

For quick inspection and local evaluation, 1-click quick-fill buttons are provided on the login page (`/login`), or you can log in manually:

| Role | Email Address | Password | Default Portal URL |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@vaishnomart.com` | `password123` | `/admin/dashboard` |
| **Water Store Vendor** | `water@vaishnomart.com` | `password123` | `/vendor/dashboard` |
| **Cloud Kitchen Vendor** | `kitchen@vaishnomart.com` | `password123` | `/vendor/dashboard` |
| **Dry Fruits Vendor** | `dryfruits@vaishnomart.com` | `password123` | `/vendor/dashboard` |
| **Delivery Rider** | `rohan@vaishnomart.com` | `password123` | `/agent/dashboard` |
| **Customer** | `customer@vaishnomart.com` | `password123` | `/customer/dashboard` |

---

## 5. Quick Start & Local Setup

### Prerequisites
- PHP 8.2 or 8.3 with extensions: `pdo_sqlite`, `pdo_mysql`, `fileinfo`, `gd`, `intl`, `zip`
- Composer 2.x

### Step 1: Clone & Install Dependencies
```bash
git clone <repository-url>
cd VaishnoMart
composer install
```

### Step 2: Environment Configuration
Copy `.env.example` to `.env` (if not already present):
```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Run Migrations & Seed Realistic Sample Data
```bash
php artisan migrate:fresh --seed
```
*Seeds 11 commercial categories, 4 verified stores in the Connaught Place commercial hub, 20+ specialized products (bubble cans with deposit, cloud kitchen meals, dry fruits, grocery), 2 registered delivery riders, customer profile with addresses, discount coupons (`WELCOME50`, `VAISHNO10`), delivery fee rules, and sample orders.*

### Step 4: Run the Development Server
```bash
php artisan serve
```
Open your browser and navigate to:
**http://127.0.0.1:8000**

---

## 6. Automated Testing Suite

VaishnoMart includes automated feature tests covering all critical operational and security invariants:
- **Authentication & RBAC:** Registration, login with email/phone, role redirect, unauthorized access prevention.
- **Vendor Matching Algorithm:** Haversine distance ranking, capacity and stock sufficiency checks, operational status filtering, auto-reassignment on rejection or timeout.
- **End-to-End Order Lifecycle:** Cart checkout, single-vendor enforcement, atomic inventory reservation, pickup OTP validation, delivery OTP validation, and financial ledger credits.
- **Security & Multi-Tenant Isolation:** Cross-customer order isolation, cross-vendor product/order isolation, and non-admin route blocking.

To execute the test suite:
```bash
php artisan test
```

Expected output:
```
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\AuthenticationTest
PASS  Tests\Feature\DatabaseSeederTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\OrderLifecycleTest
PASS  Tests\Feature\SecurityAuthorizationTest
PASS  Tests\Feature\VendorMatchingTest

Tests:    20 passed (92 assertions)
Duration: ~8s
```

---

## 7. Key Environment Variables Reference

| Variable | Default Value | Description |
| :--- | :--- | :--- |
| `APP_NAME` | `"VaishnoMart"` | Platform display name |
| `APP_TIMEZONE` | `Asia/Kolkata` | Application timezone |
| `DB_CONNECTION` | `sqlite` | Database driver (`sqlite` or `mysql`) |
| `OTP_EXPIRY_MINUTES` | `10` | Lifetime of pickup and delivery OTPs |
| `VENDOR_ACCEPTANCE_TIMEOUT_MINUTES` | `5` | Time before an unaccepted order is auto-reassigned |
| `DEFAULT_COMMISSION_PERCENTAGE` | `5.00` | Default platform commission percentage on vendor sales |

---

## 8. RESTful API Endpoints

In addition to the Blade web dashboards, VaishnoMart exposes authenticated REST APIs:

- **Authentication:** `POST /api/login`, `POST /api/register`, `POST /api/logout`
- **Catalogue:** `GET /api/categories`, `GET /api/vendors/nearby?lat=...&lng=...`, `GET /api/products`
- **Orders:** `GET /api/orders`, `POST /api/orders/checkout`, `GET /api/orders/{id}`

---

## 9. License & Credits

Developed for **VaishnoMart Multi-Vendor Hyperlocal Delivery Platform**.
Built with Laravel 11.
