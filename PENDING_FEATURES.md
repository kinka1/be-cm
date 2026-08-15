# Fitur Yang Belum Dikerjakan / Belum Lengkap

Dokumen ini merangkum fitur yang belum dikerjakan, belum aktif penuh, atau masih perlu diselaraskan antara frontend `fe-cm` dan backend `be-cm`.

## 1. Auth Guard Dan Role-Based Access

Status: belum aktif penuh

Catatan:
- Frontend sudah memiliki auth context, token storage, dan role helper.
- Route guard frontend masih dinonaktifkan dengan `AUTH_GUARD_DISABLED = true`.
- Backend belum menerapkan middleware role-based access pada endpoint penting.
- Endpoint admin/supervisor/operator masih bisa diakses tanpa pembatasan role.

Yang perlu dikerjakan:
- Aktifkan protected route frontend.
- Aktifkan halaman login sebagai flow utama.
- Tambahkan middleware `auth:sanctum` pada endpoint private.
- Tambahkan role middleware untuk admin/supervisor/operator.
- Samakan mapping role frontend dan backend.

## 2. Sinkronisasi Nama Role

Status: belum selaras

Frontend menggunakan:
- `admin`
- `kasir`
- `user`

Backend/seeder saat ini menggunakan:
- `admin`
- `supervisor`
- `operator`

Yang perlu dikerjakan:
- Putuskan standar role final.
- Update seeder role.
- Update mapping role frontend.
- Update guard/authorization backend.
- Update dokumentasi Swagger jika role sudah final.

## 3. Attendance / Absensi

Status: database sudah ada, API belum ada

Yang sudah ada:
- Migration tabel `attendances`.

Yang belum ada:
- Endpoint CRUD/flow absensi.
- Clock in.
- Clock out.
- Upload/URL foto absensi.
- Validasi 1 employee hanya 1 clock in per hari.
- Auto clock out sebelum/lewat jam 00:00.
- Report absensi.
- Filter absensi per employee/tanggal/status.

Endpoint yang disarankan:
- `GET /api/attendances`
- `POST /api/attendances/clock-in`
- `POST /api/attendances/clock-out`
- `GET /api/attendances/{id}`
- `PATCH /api/attendances/{id}`
- `GET /api/attendance-report`

## 4. Recipe Management

Status: tabel dan model ada, API CRUD belum ada

Yang sudah ada:
- Tabel `recipes`.
- Model `Recipe`.
- Recipe dipakai untuk deduksi stok POS.

Yang belum ada:
- CRUD recipe.
- UI/API untuk mengatur komposisi menu.
- Validasi agar menu dan ingredient valid.
- Endpoint melihat recipe per product/menu.

Endpoint yang disarankan:
- `GET /api/recipes`
- `POST /api/recipes`
- `GET /api/recipes/{id}`
- `PUT /api/recipes/{id}`
- `DELETE /api/recipes/{id}`
- `GET /api/products/{product}/recipes`

## 5. Table / QR Table Management

Status: tabel ada, API CRUD belum ada

Yang sudah ada:
- Tabel `calon_mantu`.
- Model `CalonMantu`.
- Endpoint menu berdasarkan QR meja sudah ada.

Yang belum ada:
- CRUD meja.
- Generate QR code.
- Update status meja.
- List meja.
- Detail meja.

Endpoint yang disarankan:
- `GET /api/tables`
- `POST /api/tables`
- `GET /api/tables/{id}`
- `PUT /api/tables/{id}`
- `DELETE /api/tables/{id}`
- `PATCH /api/tables/{id}/status`

## 6. Employee Management Belum Sepenuhnya Selaras Dengan Frontend

Status: sebagian sudah ada

Yang sudah ada:
- CRUD employee.
- Create employee sekaligus create user.
- Upload KTP dan KK.
- Login menggunakan username.

Yang belum lengkap:
- Update username employee/user.
- Update password baru saat edit employee.
- Hapus user terkait saat employee dihapus atau dinonaktifkan.
- Response employee belum selalu menyertakan data user dan role.
- Frontend `FEATURES.md` belum mencantumkan KTP/KK, padahal backend sudah mewajibkan.

Yang perlu dikerjakan:
- Tambahkan update username.
- Tambahkan update password opsional.
- Tambahkan relasi employee ke role dan user.
- Sesuaikan frontend form employee dengan field backend.
- Putuskan delete employee: soft delete, inactive, atau hard delete.

## 7. Payment Midtrans Belum Final Production

Status: flow dasar sudah ada

Yang sudah ada:
- Config Midtrans di `.env`.
- Service create QRIS transaction.
- Webhook Midtrans.
- Signature validation.
- Payment success mengubah order ke `preparing`.
- Stock deduction saat payment success.

Yang belum ada:
- Credential Midtrans asli.
- Testing webhook sandbox end-to-end.
- Handling detail status Midtrans yang lebih lengkap.
- Simpan QR string/action URL secara eksplisit jika diperlukan frontend.
- Retry/reconcile payment jika webhook terlambat.
- Endpoint cek status payment manual.

Endpoint yang disarankan:
- `GET /api/payments/{payment}`
- `GET /api/orders/{order}/payment`
- `POST /api/payments/{payment}/sync-midtrans`

## 8. POS Order Masih Belum Punya Semua Operasi Manajemen Item

Status: create order sudah ada

Yang sudah ada:
- Create QR order.
- Create cashier order.
- List order.
- Detail order.
- Update status order.

Yang belum ada:
- Tambah item ke existing order.
- Update item order.
- Hapus item order.
- Cancel order dengan rollback/reservation logic jika diperlukan.
- Refund/cancel payment.
- Reprint receipt / struktur receipt.

Endpoint yang disarankan:
- `POST /api/pos/orders/{order}/items`
- `PATCH /api/pos/orders/{order}/items/{item}`
- `DELETE /api/pos/orders/{order}/items/{item}`
- `PATCH /api/pos/orders/{order}/cancel`

## 9. Stock Opname Dan Low Stock Alert

Status: belum ada

Yang sudah ada:
- Stock report.
- Stock transaction.
- Minimum stock tersedia di products.

Yang belum ada:
- Low stock alert endpoint.
- Stock opname.
- Approval adjustment.
- Export stock report.
- Filter stock report.
- Supplier/purchase order.

Endpoint yang disarankan:
- `GET /api/stock-alerts`
- `POST /api/stock-opname`
- `GET /api/stock-opname`
- `GET /api/stock-report/export`

## 10. Dashboard API Khusus

Status: frontend menghitung dari beberapa endpoint

Yang sudah ada:
- Frontend mengambil products, orders, stock-report.

Yang belum ada:
- Endpoint dashboard ringkasan khusus.
- Endpoint sales summary.
- Endpoint order summary.
- Endpoint low stock summary.

Endpoint yang disarankan:
- `GET /api/dashboard/summary`
- `GET /api/dashboard/sales`
- `GET /api/dashboard/low-stock`
- `GET /api/dashboard/recent-orders`

## 11. Swagger Belum Lengkap Untuk Semua Detail

Status: sudah ada, perlu diperluas

Yang sudah ada:
- Swagger UI.
- Dokumentasi endpoint utama.
- POS menu dan order sudah masuk docs.

Yang belum lengkap:
- Schema response detail.
- Schema model Product, Employee, Order, Payment.
- Error response standar.
- Endpoint future seperti attendance, recipe, table management.
- Security requirement pada endpoint private setelah auth guard aktif.

## 12. Standardisasi Error Response

Status: belum konsisten penuh

Yang sudah ada:
- Banyak response sukses memakai format `{status, message, data}`.

Yang belum lengkap:
- Laravel validation error masih default.
- 404 response masih default.
- Exception global belum distandarkan.
- Semua error belum memakai `{status: "gagal", message, data}`.

Yang perlu dikerjakan:
- Tambahkan exception rendering di Laravel.
- Standarkan validation error.
- Standarkan unauthenticated dan unauthorized response.

## 13. Testing Masih Minimal

Status: masih default test

Yang sudah ada:
- Test bawaan Laravel masih pass.

Yang belum ada:
- Feature test auth.
- Feature test role CRUD.
- Feature test employee create dengan upload.
- Feature test product/category CRUD.
- Feature test stock transaction.
- Feature test POS cashier order.
- Feature test QR order.
- Feature test Midtrans webhook.
- Test stock deduction berdasarkan recipe.

## 14. Seeder Dan Demo Data Perlu Diperluas

Status: sudah ada dasar

Yang sudah ada:
- Seeder role.
- Seeder dummy employee/user.
- Seeder category/product/recipe.
- Seeder stok awal.
- Seeder contoh order.

Yang belum ada:
- Seeder attendance.
- Seeder table QR dengan format QR final.
- Seeder payment QRIS dummy.
- Seeder low stock case.
- Seeder recipe lebih lengkap.
- Seeder dashboard scenario.

## 15. File Upload Dan Storage

Status: dasar sudah ada untuk KTP/KK

Yang belum lengkap:
- Validasi ukuran final sesuai kebutuhan.
- Hapus file lama saat update KTP/KK.
- Private storage untuk dokumen sensitif.
- Authorization akses file KTP/KK.
- Cloud storage jika production.

Catatan:
- KTP dan KK sebaiknya tidak dibiarkan public untuk production.
- Lebih aman memakai private disk dan endpoint download terproteksi.

## Prioritas Rekomendasi

Prioritas 1:
- Aktifkan auth guard dan role access.
- Selaraskan role frontend/backend.
- Lengkapi employee update username/password.
- Lengkapi Midtrans sandbox end-to-end.

Prioritas 2:
- Attendance API.
- Recipe CRUD.
- Table/QR table CRUD.
- Standardisasi error response.

Prioritas 3:
- Dashboard summary API.
- Low stock alert.
- Stock opname.
- Test suite lengkap.
