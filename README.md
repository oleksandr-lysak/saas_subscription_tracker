# SaaS Subscription Tracker

## Description

This application allows you to manage SaaS subscriptions, calculate expenses considering different currencies, billing frequencies, and start dates. All aggregated data is converted to the user's base currency. The interface is built with Vue 3 + TailwindCSS via Inertia.js, backend — Laravel 12, database — MySQL.

---

## Quick Start (Docker)

1. **Clone the repository:**
   ```bash
   git clone <your-repo-url>
   cd saas_subscription_tracker
   ```
2. **Start Docker containers:**
   ```bash
   docker-compose up -d
   ```
3. **Install dependencies:**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app npm install
   ```
4. **Run migrations:**
   ```bash
   docker-compose exec app php artisan migrate
   ```
5. **Start frontend (Vite):**
   ```bash
   docker-compose exec app npm run dev
   ```
6. **Open in browser:**
   - http://localhost:8080

---

## Main Features
- Add, edit, delete subscriptions.
- Select base currency (stored in browser).
- Automatic conversion of all amounts to the base currency.
- Filtering by frequency, price, and month.
- Aggregated expenses for 30/365 days.
- Top-5 most expensive subscriptions.
- Grouped statistics and average price per currency.
- Modern responsive interface (TailwindCSS).
- Unit tests for business logic calculations.

---

## Implementation Approach
- **Backend:** Laravel 12, Eloquent ORM, Form Request validation, PSR-12 style (Laravel Pint).
- **Frontend:** Vue 3 + Inertia.js, TailwindCSS, SPA architecture.
- **Docker:** for easy local setup (MySQL, PHP, Node).
- **Base currency:** stored in localStorage.
- **All calculations:** take into account start date, frequency, currency conversion (hardcoded rates).
- **Tests:** cover main business logic (projectedExpense, getTotals, getRateBetween).

---

## Known Limitations and Ideas for Improvement
- No authentication (can be added for multi-user version).
- Currency rates are hardcoded (can be replaced with dynamic API).
- No pagination for a large number of subscriptions.
- All settings are stored only in the browser (can be saved in user profile).

---

## Running Tests

```bash
docker-compose exec app php artisan test
```

---

## Contacts

Author: Oleksandr Lysak, email: lysak.olexandr@gmail.com
