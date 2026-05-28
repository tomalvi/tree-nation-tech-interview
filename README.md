# 🌳 Tree Nation — Visit Tracker

A web service that tracks shop visits and plants trees for customers. Every time a customer visits the shop, a physical device sends an event to this service. After every **X visits**, a tree is planted on behalf of that customer.

---

## Tech Stack

- **Backend:**  Laravel 13
- **Frontend:** Vue.js 3 (Vite) + TailwindCSS
- **Database:** MySQL
- **HTTP Client:** Axios

---

## Getting Started

### Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL

### Installation

```bash
# Clone the repository
git clone https://github.com/your-username/tree-nation.git
cd tree-nation

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate
```

### Database setup

Create a MySQL database and update your `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tree_nation
DB_USERNAME=root
DB_PASSWORD=root
VISITS_PER_TREE=5

```

Then run migrations and seed sample customers:

```bash
php artisan migrate
php artisan db:seed --class=CustomerSeeder
```

### Run the project

```bash
# In one terminal — Laravel backend
php artisan serve

# In another terminal — Vite frontend
npm run dev
```

Open [http://localhost:8000] in your browser.

---

## Configuration

The number of visits required to plant a tree is configurable via `.env`:

```env
VISITS_PER_TREE=5
```

Default value is **5** if not set.

---

## API Reference

### Register a visit

```
POST /api/visits
```

**Body:**
```json
{
  "customer_id": 1
}
```

**Response `201`:**
```json
{
  "message": "Visit registered",
  "total_visits": 5,
  "trees_planted": 1
}
```

This endpoint is intended to be called by the physical device when a customer enters the shop.

---

### List customers

```
GET /api/customers
```

Returns all customers with their visit count, trees planted, and last visit timestamp.

**Response `200`:**
```json
[
  {
    "id": 1,
    "name": "Tomas Almonte",
	"email": "YPjbpGYhTR@example.com",
    "trees_planted": 1,
    "last_visit_at": "2025-05-27T10:30:00",
    "created_at": "2026-05-28T11:34:00.000000Z",
	"updated_at": "2026-05-28T15:45:21.000000Z",
    "visits_count": 5
  }
]

```

---

### Visits aggregated by hour

```
GET /api/visits/hourly
```

Returns the number of visits grouped by hour of the day. Used to render the frontend chart.

**Response `200`:**
```json
[
  { "hour": 9, "total": 3 },
  { "hour": 10, "total": 7 },
  { "hour": 11, "total": 2 }
]
```

---

## Frontend

The dashboard is available at [http://localhost:8000](http://localhost:8000) and includes:

- **Stats overview** — total customers, visits, and trees planted
- **Shop entrance simulator** — animated automatic glass door that sends a visit event for a random customer on each click (simulates the physical device)
- **Visits per hour chart** — bar chart showing visit distribution throughout the day
- **Customer table** — all customers with their stats and last visit time

---

## Assumptions

- Each customer is pre-registered in the system. The physical device sends the `customer_id` when detecting an entry.
- The tree counter increments every X visits exactly (e.g. on visit 5, 10, 15...). Partial cycles do not count.
- `last_visit_at` is stored directly on the customer record for fast access, as the spec explicitly requires it.
- Visits are stored with timezone `Europe/Madrid` (configurable via "config/app.php `'timezone' => 'Europe/Madrid'`).
- The hourly aggregation is based on the current day's data.

---

## Project Structure

```
app/
  Http/Controllers/
    VisitController.php      # POST /api/visits, GET /api/visits/hourly
    CustomerController.php   # GET /api/customers
  Models/
    Customer.php
    Visit.php
resources/
  js/
    App.vue                  # Main Vue dashboard
  views/
    dashboard.blade.php      # Inertia entry point
database/
  migrations/
    customers_table
    visits_table
  seeders/
    CustomerSeeder.php
  factories/
    CustomerFactory.php
```

