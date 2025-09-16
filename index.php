<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RuangClient - Sistem Manajemen Client Modern</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: transform 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Hero Section -->
    <section class="gradient-bg min-h-screen flex items-center justify-center px-4">
        <div class="text-center text-white max-w-4xl mx-auto">
            <div class="mb-8">
                <i class="fas fa-users text-6xl mb-4"></i>
                <h1 class="text-4xl md:text-6xl font-bold mb-6">RuangClient</h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">
                    Sistem Manajemen Client Modern untuk Bisnis Anda
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                <a href="public/" class="bg-white text-gray-800 py-4 px-8 rounded-xl font-semibold hover:bg-gray-100 transition duration-300 card-hover">
                    <i class="fas fa-globe text-2xl mb-2"></i>
                    <div>Website Client</div>
                    <small class="text-gray-600">Booking & Informasi</small>
                </a>
                
                <a href="dashboard/" class="bg-white text-gray-800 py-4 px-8 rounded-xl font-semibold hover:bg-gray-100 transition duration-300 card-hover">
                    <i class="fas fa-chart-dashboard text-2xl mb-2"></i>
                    <div>Dashboard Bisnis</div>
                    <small class="text-gray-600">Kelola Client & Booking</small>
                </a>
            </div>
            
            <div class="mt-12 text-center">
                <a href="admin/" class="text-white hover:text-gray-200 text-sm underline">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Admin Panel
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Fitur Lengkap</h2>
                <p class="text-gray-600 text-lg">Solusi lengkap untuk manajemen client modern</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Booking System</h3>
                    <p class="text-gray-600">Sistem booking otomatis dengan kalender terintegrasi dan notifikasi real-time.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="text-green-600 text-4xl mb-4">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Payment Gateway</h3>
                    <p class="text-gray-600">Integrasi Midtrans untuk pembayaran yang aman dan mudah.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="text-purple-600 text-4xl mb-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Laporan Keuangan</h3>
                    <p class="text-gray-600">Dashboard analitik lengkap untuk monitoring bisnis Anda.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="text-red-600 text-4xl mb-4">
                        <i class="fas fa-link"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Link Tree</h3>
                    <p class="text-gray-600">Halaman link terpusat untuk semua sosial media dan kontak Anda.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="text-yellow-600 text-4xl mb-4">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Mobile First</h3>
                    <p class="text-gray-600">Desain responsif yang dioptimalkan untuk pengalaman mobile terbaik.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="text-indigo-600 text-4xl mb-4">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Client Management</h3>
                    <p class="text-gray-600">Kelola data client dengan mudah dan efisien dalam satu platform.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20 bg-gray-100 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Harga Berlangganan</h2>
            <p class="text-gray-600 text-lg mb-12">Investasi terbaik untuk bisnis yang berkembang</p>
            
            <div class="bg-white p-8 rounded-xl shadow-lg max-w-md mx-auto">
                <div class="text-4xl font-bold text-blue-600 mb-2">Rp 48.000</div>
                <div class="text-gray-600 mb-6">per bulan</div>
                
                <ul class="text-left mb-8 space-y-3">
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-3"></i>
                        Website Client Unlimited
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-3"></i>
                        Dashboard Lengkap
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-3"></i>
                        Payment Gateway Terintegrasi
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-3"></i>
                        Support 24/7
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-3"></i>
                        Update Fitur Gratis
                    </li>
                </ul>
                
                <a href="dashboard/register.php" class="w-full bg-blue-600 text-white py-3 px-6 rounded-xl font-semibold hover:bg-blue-700 transition duration-300 inline-block">
                    Mulai Berlangganan
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12 px-4">
        <div class="max-w-6xl mx-auto text-center">
            <div class="mb-8">
                <h3 class="text-2xl font-bold mb-4">RuangClient</h3>
                <p class="text-gray-400">Sistem Manajemen Client Modern untuk Bisnis Indonesia</p>
            </div>
            
            <div class="border-t border-gray-700 pt-8">
                <p class="text-gray-400">&copy; 2025 RuangClient. All rights reserved. Developed by Yuda.</p>
            </div>
        </div>
    </footer>
</body>
</html>