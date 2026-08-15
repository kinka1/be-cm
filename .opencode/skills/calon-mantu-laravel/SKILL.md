---
name: calon-mantu-laravel
description: Use when working on the Calon Mantu Laravel API project, especially auth, employee management, POS orders, Midtrans QRIS, asset/stock management, migrations, Swagger, and MySQL business rules.
---

# Calon Mantu Laravel Project

Use this skill when modifying or reviewing this project's backend implementation.

## Project Context

- Framework: Laravel 12.
- Database: MySQL.
- Auth: Laravel Sanctum personal access tokens.
- API response format should generally be:
  - Success: `{ "status": "sukses", "message": "...", "data": ... }`
  - Failure: `{ "status": "gagal", "message": "...", "data": ... }`
- API docs: L5 Swagger using PHP attributes in `app/Docs/ApiDocs.php`.
- Swagger UI route: `/api/documentation`.
- POS payment gateway target: Midtrans QRIS.

## Main Modules

- Auth:
  - Login uses `username` and `password`.
  - `users.employee_id` links auth users to employees.
- Employee management:
  - Supervisor creates employees with `full_name`, `email`, `join_date`, `role_id`, `username`, `password`, `ktp`, and `kk`.
  - `ktp` and `kk` are image uploads stored on the public disk.
  - `position` was intentionally removed; role is represented by `role_id`.
- Roles:
  - `roles` has `role_name` and `permissions`.
  - Permissions are currently not implemented; seed values use empty permissions.
- Asset/stock management:
  - `products`, `categories`, `recipes`, `stock_transactions`, and `stock_report` are the core tables.
  - Negative manual stock is allowed for generic asset management, but POS checkout must reject orders when product/ingredient stock is insufficient.
  - `products` uses soft deletes.
- POS:
  - QR orders must pay via Midtrans QRIS before entering `preparing`.
  - Cashier orders can be `cash` or `qris`.
  - Stok deduction happens on payment success, not when order status becomes `completed`.
  - `completed` means the order has been handed to the customer.
  - The old completed-order stock trigger should remain disabled to avoid double stock deduction.

## Key Routes

- Auth:
  - `POST /api/auth/register`
  - `POST /api/auth/login`
  - `GET /api/me`
  - `POST /api/auth/logout`
- Roles:
  - `Route::apiResource('roles', RoleController::class)`
- Employees:
  - `Route::apiResource('employees', EmployeeController::class)`
- Assets:
  - `Route::apiResource('categories', CategoryController::class)`
  - `Route::apiResource('products', ProductController::class)`
  - `GET /api/stock-report`
  - `GET|POST /api/stock-transactions`
- POS:
  - `GET /api/pos/menu`
  - `GET /api/pos/tables/{qr_code}/menu`
  - `POST /api/pos/qr-orders`
  - `POST /api/pos/cashier-orders`
  - `GET /api/pos/orders`
  - `GET /api/pos/orders/{order}`
  - `PATCH /api/pos/orders/{order}/status`
  - `POST /api/payments/midtrans/webhook`

## Implementation Guidelines

- Prefer small, focused changes.
- Keep controllers thin; put business logic in services under `app/Services`.
- Use Form Request classes for non-trivial validation.
- Use DB transactions for multi-table writes such as employee creation, POS order creation, payment updates, and stock deduction.
- Keep Swagger docs in sync by updating `app/Docs/ApiDocs.php` and running `php artisan l5-swagger:generate`.
- After migration changes, run `php artisan migrate` where appropriate.
- Run `php artisan route:list` after route changes.
- Run `php artisan test` after functional changes.

## Midtrans Notes

- Credentials are read from `.env`:
  - `MIDTRANS_SERVER_KEY`
  - `MIDTRANS_CLIENT_KEY`
  - `MIDTRANS_MERCHANT_ID`
  - `MIDTRANS_IS_PRODUCTION`
- Local development webhook needs a public tunnel such as ngrok or Cloudflare Tunnel.
- Webhook signature validation uses the Midtrans server key.

## Migration Cautions

- MySQL trigger order matters. Avoid re-enabling completed-order stock deduction unless the POS stock flow is redesigned.
- Several tables intentionally do not have full Laravel timestamps, so set `$timestamps = false` or `const UPDATED_AT = null` in models when needed.
- `calon_mantu` is the table for cafe tables/QR seating. Use model `CalonMantu` with `$table = 'calon_mantu'`.

## Verification Checklist

Run these after relevant changes:

```bash
php artisan route:list
php artisan test
php artisan l5-swagger:generate
```
