# RuangClient - Sistem Manajemen Client Modern

## Deskripsi
RuangClient adalah sistem manajemen client yang lengkap untuk bisnis modern. Sistem ini menyediakan website client (seperti Linktree), dashboard pemilik usaha, dan admin panel dengan fitur-fitur canggih.

## Fitur Utama

### 🌐 Website Client (Public)
- Halaman seperti Linktree dengan design mobile-first
- Form booking lengkap dengan integrasi pembayaran
- Informasi layanan dan kontak
- Responsive design yang optimal di semua perangkat

### 📊 Dashboard Pemilik Usaha
- Statistik booking dan pembayaran real-time
- Manajemen client dan booking (CRUD)
- Calendar view untuk jadwal
- Laporan keuangan lengkap
- Pengaturan profile dan sosial media
- Integrasi Midtrans untuk pembayaran
- Sistem berlangganan bulanan

### 🛡️ Admin Panel
- Monitoring semua user dan bisnis
- Laporan pembayaran dan revenue
- Manajemen berlangganan
- Pengaturan sistem global

## Teknologi
- **Backend**: PHP Native dengan MySQLi
- **Frontend**: HTML, CSS (Tailwind), JavaScript
- **Payment Gateway**: Midtrans
- **Database**: MySQL
- **Design**: Mobile-first responsive

## Instalasi

1. Clone atau download project
2. Import database schema dari `database/schema.sql`
3. Konfigurasi database di `config/database.php`
4. Download library Midtrans (otomatis via shell command)
5. Akses aplikasi:
   - Halaman utama: `http://localhost/ruangclient/`
   - Website client: `http://localhost/ruangclient/public/`
   - Dashboard bisnis: `http://localhost/ruangclient/dashboard/`
   - Admin panel: `http://localhost/ruangclient/admin/`

## Default Login

### Admin Panel
- Username: `yuda_admin`
- Password: `admin123`

### Dashboard Demo
- Username: `demo_business`
- Password: `demo123`

## Struktur File
```
ruangclient/
├── config/
│   ├── database.php      # Konfigurasi database
│   └── midtrans.php      # Konfigurasi Midtrans
├── public/
│   ├── index.php         # Website client (Linktree)
│   ├── booking.php       # Form booking
│   └── payment.php       # Halaman pembayaran
├── dashboard/
│   ├── index.php         # Dashboard utama
│   ├── login.php         # Login pemilik usaha
│   ├── register.php      # Registrasi & berlangganan
│   ├── clients.php       # Manajemen client
│   └── profile.php       # Pengaturan profile
├── admin/
│   ├── index.php         # Admin dashboard
│   └── login.php         # Login admin
├── database/
│   └── schema.sql        # Database schema
└── vendor/               # Library Midtrans
```

## Harga Berlangganan
- **Biaya**: Rp 48.000 per bulan
- **Fitur**: Website client unlimited, dashboard lengkap, payment gateway, support 24/7

## Fitur Unggulan
- ✅ Design mobile-first seperti aplikasi
- ✅ Integrasi payment gateway Midtrans
- ✅ Sistem berlangganan otomatis
- ✅ Calendar booking terintegrasi
- ✅ Laporan keuangan real-time
- ✅ Link sosial media (Linktree)
- ✅ Management client CRUD lengkap
- ✅ Admin panel monitoring
- ✅ Responsive semua perangkat

## Kontribusi
Developed by **Yuda** - RuangClient System

## License
Proprietary - All rights reserved