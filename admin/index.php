<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Get admin data
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Get statistics
$stats = [];

// Total Business Owners
$result = $conn->query("SELECT COUNT(*) as total FROM business_owners");
$stats['total_businesses'] = $result->fetch_assoc()['total'];

// Active Subscriptions
$result = $conn->query("SELECT COUNT(*) as total FROM business_owners WHERE subscription_status = 'active'");
$stats['active_subscriptions'] = $result->fetch_assoc()['total'];

// Total Revenue
$result = $conn->query("SELECT SUM(amount) as total FROM subscriptions WHERE payment_status = 'paid'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Monthly Revenue
$result = $conn->query("SELECT SUM(amount) as total FROM subscriptions WHERE payment_status = 'paid' AND MONTH(payment_date) = MONTH(CURRENT_DATE) AND YEAR(payment_date) = YEAR(CURRENT_DATE)");
$stats['monthly_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent Business Owners
$result = $conn->query("SELECT * FROM business_owners ORDER BY created_at DESC LIMIT 5");
$recent_businesses = $result->fetch_all(MYSQLI_ASSOC);

// Recent Subscriptions
$result = $conn->query("
    SELECT s.*, b.business_name, b.owner_name 
    FROM subscriptions s 
    JOIN business_owners b ON s.business_owner_id = b.id 
    ORDER BY s.created_at DESC 
    LIMIT 10
");
$recent_subscriptions = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - RuangClient</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%);
        }
        .stat-card {
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="gradient-bg text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <i class="fas fa-shield-alt text-2xl mr-3"></i>
                    <div>
                        <h1 class="text-2xl font-bold">Admin Panel RuangClient</h1>
                        <p class="text-sm opacity-75">Dashboard Administrator</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Selamat datang, <?= htmlspecialchars($admin['name']) ?></span>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 stat-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-building text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Bisnis</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_businesses'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 stat-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Berlangganan Aktif</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['active_subscriptions'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 stat-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Revenue Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-900">Rp <?= number_format($stats['monthly_revenue'], 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 stat-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-money-bill-wave text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Business Owners -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-users text-blue-600 mr-2"></i>
                        Bisnis Terbaru
                    </h2>
                    <a href="businesses.php" class="text-blue-600 hover:text-blue-800 font-semibold">
                        Lihat Semua
                    </a>
                </div>
                
                <?php if ($recent_businesses): ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_businesses as $business): ?>
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                    <?= strtoupper(substr($business['business_name'], 0, 1)) ?>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($business['business_name']) ?></div>
                                    <div class="text-sm text-gray-600"><?= htmlspecialchars($business['owner_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= date('d M Y', strtotime($business['created_at'])) ?></div>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $business['subscription_status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= ucfirst($business['subscription_status']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">Belum ada bisnis terdaftar</p>
                <?php endif; ?>
            </div>

            <!-- Recent Subscriptions -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-credit-card text-green-600 mr-2"></i>
                        Pembayaran Terbaru
                    </h2>
                    <a href="subscriptions.php" class="text-blue-600 hover:text-blue-800 font-semibold">
                        Lihat Semua
                    </a>
                </div>
                
                <?php if ($recent_subscriptions): ?>
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        <?php foreach ($recent_subscriptions as $subscription): ?>
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($subscription['business_name']) ?></div>
                                    <div class="text-sm text-gray-600"><?= htmlspecialchars($subscription['owner_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= date('d M Y H:i', strtotime($subscription['created_at'])) ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">Rp <?= number_format($subscription['amount'], 0, ',', '.') ?></div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $subscription['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : ($subscription['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= ucfirst($subscription['payment_status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">Belum ada pembayaran</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">
                <i class="fas fa-bolt text-yellow-600 mr-2"></i>
                Aksi Cepat
            </h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="businesses.php" class="p-4 bg-blue-600 text-white rounded-xl text-center font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-building text-2xl mb-2 block"></i>
                    <div class="text-sm">Kelola Bisnis</div>
                </a>
                
                <a href="subscriptions.php" class="p-4 bg-green-600 text-white rounded-xl text-center font-semibold hover:bg-green-700 transition">
                    <i class="fas fa-credit-card text-2xl mb-2 block"></i>
                    <div class="text-sm">Pembayaran</div>
                </a>
                
                <a href="settings.php" class="p-4 bg-purple-600 text-white rounded-xl text-center font-semibold hover:bg-purple-700 transition">
                    <i class="fas fa-cog text-2xl mb-2 block"></i>
                    <div class="text-sm">Pengaturan</div>
                </a>
                
                <a href="reports.php" class="p-4 bg-orange-600 text-white rounded-xl text-center font-semibold hover:bg-orange-700 transition">
                    <i class="fas fa-chart-bar text-2xl mb-2 block"></i>
                    <div class="text-sm">Laporan</div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>