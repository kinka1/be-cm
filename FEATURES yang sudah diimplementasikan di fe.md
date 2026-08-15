# Daftar Fitur Yang Sudah Diimplementasikan

Dokumen ini berisi daftar fitur yang sudah tersedia pada frontend `fe-cm` berdasarkan route, halaman, dan integrasi API yang ada di source code.

## Ringkasan Aplikasi

Aplikasi ini adalah frontend POS berbasis React, Vite, TypeScript, Tailwind CSS, React Router, TanStack Query, dan Axios.

Modul yang sudah tersedia:

- Dashboard admin
- POS kasir
- Manajemen order
- Manajemen produk
- Manajemen kategori
- Manajemen stok
- Manajemen karyawan
- Manajemen supplier
- Purchase order
- Stock adjustment
- Stock opname
- Product batch dan expiry
- Order pelanggan berbasis QR
- Auth, token, dan role access

## Route Yang Tersedia

| Route | Halaman | Role | Keterangan |
| --- | --- | --- | --- |
| `/` | Dashboard | Admin | Ringkasan operasional admin |
| `/pos` | POS Kasir | Admin, Kasir | Order kasir dan keranjang transaksi |
| `/orders` | Orders | Admin, Kasir | Monitoring dan update status order |
| `/products` | Products | Admin | CRUD produk |
| `/categories` | Categories | Admin | CRUD kategori |
| `/stock` | Stock | Admin | Transaksi stok dan laporan stok |
| `/employees` | Employees | Admin | CRUD karyawan |
| `/suppliers` | Suppliers | Admin | CRUD supplier |
| `/purchase-orders` | Purchase Orders | Admin | Pembuatan, receive, dan cancel PO |
| `/stock-adjustments` | Stock Adjustments | Admin | Pengajuan dan approval adjustment stok |
| `/stock-opnames` | Stock Opname | Admin | Pencatatan stok fisik dan approval opname |
| `/product-batches` | Product Batches | Admin | Batch produk dan tanggal kedaluwarsa |
| `/u` | User Order | Publik | Order pelanggan dengan input QR manual |
| `/u/:qrCode` | User Order | Publik | Order pelanggan berdasarkan QR meja |
| `/order` | User Order | Publik | Alias halaman order pelanggan |
| `/order/:qrCode` | User Order | Publik | Alias order pelanggan berdasarkan QR meja |
| `/unauthorized` | Unauthorized | Semua | Halaman akses tidak tersedia |
| `/login` | Login | Semua | Saat ini diarahkan ke `/` karena auth guard dinonaktifkan |

## Dashboard Admin

Fitur yang sudah ada:

- Menampilkan total order terbaru.
- Menampilkan jumlah produk aktif.
- Menampilkan jumlah produk low stock.
- Menghitung total paid sales dari order dengan status pembayaran `paid`.
- Menampilkan daftar order terakhir.
- Menampilkan daftar produk low stock.
- Mengambil data produk, order, dan laporan stok dari backend.

Endpoint yang digunakan:

- `GET /products`
- `GET /pos/orders`
- `GET /stock-report`

## POS Kasir

Fitur yang sudah ada:

- Menampilkan menu produk untuk kasir.
- Pencarian produk atau SKU.
- Filter menu berdasarkan kategori.
- Tambah produk ke cart.
- Tambah dan kurangi quantity item.
- Hapus item dari cart.
- Tambah catatan per item.
- Pilih tipe order `dine_in_cashier` atau `takeaway`.
- Input nama customer.
- Input Table ID untuk order dine-in.
- Pilih metode pembayaran `cash` atau `qris`.
- Input diskon.
- Input nominal pembayaran untuk cash.
- Hitung subtotal.
- Hitung total setelah diskon.
- Hitung kembalian untuk pembayaran cash.
- Submit order kasir ke backend.
- Refresh data order, produk, dan stock report setelah order berhasil dibuat.

Endpoint yang digunakan:

- `GET /categories`
- `GET /pos/menu`
- `POST /pos/cashier-orders`

## Manajemen Orders

Fitur yang sudah ada:

- Menampilkan daftar order.
- Filter order berdasarkan order status.
- Filter order berdasarkan payment status.
- Melihat detail order.
- Menampilkan item dalam order.
- Menampilkan quantity, harga satuan, subtotal, dan catatan item.
- Update status order.

Status order yang didukung:

- `pending`
- `preparing`
- `ready`
- `completed`
- `cancelled`

Status pembayaran yang didukung:

- `pending`
- `paid`
- `cancelled`

Endpoint yang digunakan:

- `GET /pos/orders`
- `GET /pos/orders/:id`
- `PATCH /pos/orders/:id/status`

## Manajemen Produk

Fitur yang sudah ada:

- Menampilkan daftar produk.
- Pencarian produk atau SKU.
- Tambah produk baru.
- Edit produk.
- Hapus produk.
- Menampilkan status produk aktif atau tidak aktif.
- Menampilkan stok produk.
- Menampilkan harga jual produk.
- Menghubungkan produk dengan kategori.

Field produk yang dikelola:

- Nama produk
- SKU
- Kategori
- Deskripsi
- Unit of measure
- Minimum stock
- Current stock
- Cost price
- Selling price
- Status aktif atau nonaktif

Endpoint yang digunakan:

- `GET /products`
- `POST /products`
- `PUT /products/:id`
- `DELETE /products/:id`
- `GET /categories`

## Manajemen Kategori

Fitur yang sudah ada:

- Menampilkan daftar kategori.
- Tambah kategori baru.
- Edit kategori.
- Hapus kategori.
- Menampilkan deskripsi kategori.

Field kategori yang dikelola:

- Nama kategori
- Deskripsi

Endpoint yang digunakan:

- `GET /categories`
- `POST /categories`
- `PUT /categories/:id`
- `DELETE /categories/:id`

## Manajemen Stok

Fitur yang sudah ada:

- Menampilkan indikator produk low stock.
- Menampilkan stock report.
- Menampilkan riwayat transaksi stok.
- Tambah transaksi stok.
- Refresh data transaksi stok, stock report, dan produk setelah transaksi stok dibuat.

Tipe transaksi stok:

- `in`
- `out`
- `adjustment`

Reference type yang tersedia:

- `purchase`
- `sale`
- `adjustment`

Data stock report yang ditampilkan:

- Nama produk
- Total stock in
- Total stock out
- Current stock
- Last transaction date

Endpoint yang digunakan:

- `GET /products`
- `GET /stock-transactions`
- `POST /stock-transactions`
- `GET /stock-report`

## Manajemen Karyawan

Fitur yang sudah ada:

- Menampilkan daftar karyawan.
- Tambah karyawan.
- Edit karyawan.
- Hapus karyawan.
- Mengatur role karyawan.
- Mengatur status karyawan.
- Input password saat membuat karyawan.
- Input password baru opsional saat edit karyawan.

Role yang tersedia:

- Admin
- Kasir
- User

Status yang tersedia:

- Active
- Inactive

Field karyawan yang dikelola:

- Nama lengkap
- Email
- Role
- Status
- Password

Endpoint yang digunakan:

- `GET /employees`
- `POST /employees`
- `PUT /employees/:id`
- `DELETE /employees/:id`

## Manajemen Supplier

Fitur yang sudah ada:

- Menampilkan daftar supplier.
- Tambah supplier.
- Edit supplier.
- Hapus supplier.
- Menampilkan status supplier aktif atau tidak aktif.
- Menampilkan informasi kontak supplier.

Field supplier yang dikelola:

- Nama supplier
- Contact name
- Phone
- Email
- Address
- Status

Endpoint yang digunakan:

- `GET /suppliers`
- `POST /suppliers`
- `PUT /suppliers/:id`
- `DELETE /suppliers/:id`

## Purchase Orders

Fitur yang sudah ada:

- Menampilkan daftar purchase order.
- Filter purchase order berdasarkan status.
- Membuat purchase order baru.
- Menambahkan item produk ke purchase order.
- Menghapus item draft pada form purchase order.
- Menghitung estimasi total purchase order.
- Menampilkan detail item purchase order.
- Receive purchase order untuk menambah stok.
- Cancel purchase order.
- Resolve nama supplier dan produk di sisi frontend jika relasi tidak dikirim backend.

Status purchase order:

- `draft`
- `ordered`
- `received`
- `cancelled`

Field purchase order yang dikelola:

- Supplier
- Order date
- Notes
- Produk item
- Quantity item
- Unit cost item
- Catatan item

Endpoint yang digunakan:

- `GET /purchase-orders`
- `GET /purchase-orders/:id`
- `POST /purchase-orders`
- `PUT /purchase-orders/:id`
- `POST /purchase-orders/:id/receive`
- `POST /purchase-orders/:id/cancel`
- `GET /suppliers`
- `GET /products`

## Stock Adjustments

Fitur yang sudah ada:

- Menampilkan daftar pengajuan adjustment stok.
- Filter adjustment berdasarkan status.
- Membuat pengajuan koreksi stok.
- Approval adjustment.
- Reject adjustment.
- Menampilkan alasan adjustment.
- Menampilkan waktu pengajuan dan waktu approval.
- Resolve nama produk di sisi frontend jika relasi produk tidak dikirim backend.

Status adjustment:

- `pending`
- `approved`
- `rejected`

Tipe adjustment:

- `increase`
- `decrease`

Field adjustment yang dikelola:

- Produk
- Quantity
- Tipe adjustment
- Requested by dari user login jika tersedia
- Reason
- Approved by dari user login jika tersedia

Endpoint yang digunakan:

- `GET /stock-adjustments`
- `POST /stock-adjustments`
- `POST /stock-adjustments/:id/approve`
- `POST /stock-adjustments/:id/reject`
- `GET /products`

## Stock Opname

Fitur yang sudah ada:

- Menampilkan daftar stock opname.
- Filter opname berdasarkan status.
- Membuat opname baru.
- Memilih opname untuk melihat detail.
- Menambah item opname saat status masih draft.
- Submit opname.
- Approve opname.
- Menampilkan stok sistem, stok fisik, dan selisih.
- Menampilkan catatan per item opname.
- Refresh data stock transaction, stock report, dan produk setelah opname disetujui.
- Resolve nama produk di sisi frontend jika relasi produk tidak dikirim backend.

Status opname:

- `draft`
- `submitted`
- `approved`
- `cancelled`

Field opname yang dikelola:

- Tanggal opname
- Catatan header
- Produk item
- Stok fisik
- Catatan item

Endpoint yang digunakan:

- `GET /stock-opnames`
- `GET /stock-opnames/:id`
- `POST /stock-opnames`
- `POST /stock-opnames/:id/items`
- `POST /stock-opnames/:id/submit`
- `POST /stock-opnames/:id/approve`
- `GET /products`

## Product Batches

Fitur yang sudah ada:

- Menampilkan daftar batch produk.
- Filter tampilan semua batch atau batch yang mendekati kedaluwarsa.
- Mengatur jumlah hari untuk filter mendekati kedaluwarsa.
- Membuat batch produk baru.
- Menampilkan batch number.
- Menampilkan quantity batch.
- Menampilkan tanggal diterima.
- Menampilkan tanggal kedaluwarsa.
- Menampilkan indikator warna expiry.
- Resolve nama produk di sisi frontend.

Field batch yang dikelola:

- Produk
- Batch number
- Quantity
- Expired date
- Received date
- Notes

Endpoint yang digunakan:

- `GET /product-batches`
- `GET /product-batches/expiring-soon`
- `POST /product-batches`
- `GET /products`

## Order Pelanggan Berbasis QR

Fitur yang sudah ada:

- Halaman publik untuk pelanggan.
- Order berdasarkan QR meja dari URL.
- Order dengan input QR meja manual jika QR tidak tersedia di URL.
- Menampilkan informasi meja.
- Menampilkan menu pelanggan.
- Pencarian menu.
- Filter menu berdasarkan kategori.
- Menampilkan stok menu.
- Menonaktifkan tombol tambah jika stok habis.
- Tambah produk ke cart.
- Tambah dan kurangi quantity.
- Hapus item dari cart.
- Input nama pemesan.
- Catatan item.
- Quick notes untuk catatan cepat.
- Submit order QR ke backend.
- Menampilkan konfirmasi order berhasil dikirim.
- Menampilkan nomor order, status order, dan total pembayaran QRIS.
- Layout responsif desktop dan mobile.

Quick notes yang tersedia:

- Level 0
- Level 1
- Level 2
- Level 3
- Level 4
- Level 5
- Tanpa bawang
- Extra pangsit
- Es sedikit

Endpoint yang digunakan:

- `GET /categories`
- `GET /pos/menu`
- `GET /pos/tables/:qrCode/menu`
- `POST /pos/qr-orders`

## Auth, Token, Dan Role Access

Fitur yang sudah ada di kode:

- Login menggunakan username dan password.
- Logout.
- Penyimpanan token di `localStorage` dengan key `pos_token`.
- Penyimpanan data user di `localStorage` dengan key `pos_user`.
- Axios request interceptor untuk mengirim `Authorization: Bearer <token>`.
- Normalisasi role user dari beberapa kemungkinan format response backend.
- Role-based access helper.
- Redirect home berdasarkan role.
- Protected route untuk admin, kasir, dan user.
- Unauthorized page untuk role yang tidak punya akses.

Role aplikasi:

- `admin`
- `kasir`
- `user`

Mapping home berdasarkan role:

- Admin: `/`
- Kasir: `/pos`
- User: `/u`

Endpoint yang digunakan:

- `POST /auth/login`
- `GET /me`
- `POST /auth/logout`

Catatan:

- Saat ini auth guard dinonaktifkan dengan `AUTH_GUARD_DISABLED = true` di routing dan layout.
- Karena guard dinonaktifkan, semua menu admin/kasir terlihat tanpa login.
- Route `/login` saat ini diarahkan ke `/`, sehingga halaman login sudah ada di kode tetapi tidak aktif sebagai flow utama aplikasi.

## Integrasi API

Base URL API diambil dari environment variable:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

Jika environment variable tidak tersedia, default yang digunakan adalah:

```text
http://127.0.0.1:8000/api
```

Endpoint yang sudah digunakan frontend:

- `POST /auth/login`
- `GET /me`
- `POST /auth/logout`
- `GET /categories`
- `POST /categories`
- `PUT /categories/:id`
- `DELETE /categories/:id`
- `GET /products`
- `POST /products`
- `PUT /products/:id`
- `DELETE /products/:id`
- `GET /pos/menu`
- `GET /pos/tables/:qrCode/menu`
- `POST /pos/qr-orders`
- `POST /pos/cashier-orders`
- `GET /pos/orders`
- `GET /pos/orders/:id`
- `PATCH /pos/orders/:id/status`
- `GET /stock-transactions`
- `POST /stock-transactions`
- `GET /stock-report`
- `GET /employees`
- `POST /employees`
- `PUT /employees/:id`
- `DELETE /employees/:id`
- `GET /suppliers`
- `POST /suppliers`
- `PUT /suppliers/:id`
- `DELETE /suppliers/:id`
- `GET /purchase-orders`
- `GET /purchase-orders/:id`
- `POST /purchase-orders`
- `PUT /purchase-orders/:id`
- `POST /purchase-orders/:id/receive`
- `POST /purchase-orders/:id/cancel`
- `GET /stock-adjustments`
- `POST /stock-adjustments`
- `POST /stock-adjustments/:id/approve`
- `POST /stock-adjustments/:id/reject`
- `GET /stock-opnames`
- `GET /stock-opnames/:id`
- `POST /stock-opnames`
- `POST /stock-opnames/:id/items`
- `POST /stock-opnames/:id/submit`
- `POST /stock-opnames/:id/approve`
- `GET /product-batches`
- `GET /product-batches/expiring-soon`
- `POST /product-batches`
- `GET /stock-alerts`
- `GET /stock-alerts/summary`

## Handling Response API

Fitur handling API yang sudah ada:

- Axios client dengan `baseURL` dari env.
- Bearer token otomatis lewat interceptor.
- Error parser untuk mengambil message validasi backend.
- Normalisasi response list melalui helper `toList()` untuk mendukung array langsung, Laravel paginator, dan nested response wrapper.

## State UI Yang Sudah Ada

Komponen state yang digunakan di berbagai halaman:

- Loading state
- Error state
- Empty state
- Toast success
- Toast error

## Komponen UI Umum

Komponen UI reusable yang tersedia:

- Button
- Input
- Select
- Textarea
- Field
- Badge

## Catatan Status Implementasi

Fitur yang sudah terlihat lengkap secara frontend:

- Dashboard
- POS kasir
- Orders
- Products
- Categories
- Stock
- Employees
- Suppliers
- Purchase Orders
- Stock Adjustments
- Stock Opname
- Product Batches
- User QR order
- API client dan error handling
- Token storage dan auth context

Fitur yang sudah tersedia di layer API tetapi belum terlihat sebagai route/menu khusus:

- Stock Alerts melalui `stockAlertsApi`

Fitur yang sudah ada tetapi belum aktif penuh dalam routing saat ini:

- Login page
- Protected route
- Role-based route guard

Alasannya karena `AUTH_GUARD_DISABLED` masih bernilai `true`.
