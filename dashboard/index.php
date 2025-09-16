<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['business_owner_id'])) {
    header('Location: login.php');
    exit;
}

$business_id = $_SESSION['business_owner_id'];

$db = new Database();
$conn = $db->getConnection();

// Get business owner data
$stmt = $conn->prepare("SELECT * FROM business_owners WHERE id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();

// Get dashboard statistics
$stats = [];

// Total Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE business_owner_id = $business_id");
$stats['total_bookings'] = $result->fetch_assoc()['total'];

// Scheduled Bookings (pending & confirmed for future dates)
$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE business_owner_id = $business_id AND (status = 'pending' OR status = 'confirmed') AND booking_date >= CURDATE()");
$stats['scheduled'] = $result->fetch_assoc()['total'];

// Completed Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE business_owner_id = $business_id AND status = 'completed'");
$stats['completed'] = $result->fetch_assoc()['total'];

// Cancelled Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE business_owner_id = $business_id AND status = 'cancelled'");
$stats['cancelled'] = $result->fetch_assoc()['total'];

// Payment Statistics
$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE business_owner_id = $business_id AND payment_status = 'unpaid'");
$stats['unpaid'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE business_owner_id = $business_id AND payment_status = 'down_payment'");
$stats['down_payment'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE business_owner_id = $business_id AND payment_status = 'paid'");
$stats['paid'] = $result->fetch_assoc()['total'];

// Recent Bookings
$stmt = $conn->prepare("
    SELECT b.*, c.name as client_name, c.whatsapp, s.service_name 
    FROM bookings b
    JOIN clients c ON b.client_id = c.id
    JOIN services s ON b.service_id = s.id
    WHERE b.business_owner_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$recent_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RuangClient</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .mobile-app {
            max-width: 400px;
            margin: 0 auto;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-card {
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="mobile-app">
        <!-- Header -->
        <div class="gradient-bg text-white p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-xl font-bold">Dashboard</h1>
                    <p class="text-sm opacity-75"><?= htmlspecialchars($business['business_name']) ?></p>
                </div>
                <div class="text-right">
                    <a href="profile.php" class="text-white">
                        <i class="fas fa-user-circle text-2xl"></i>
                    </a>
                </div>
            </div>
            
            <div class="text-center">
                <p class="text-sm opacity-75">Selamat datang,</p>
                <p class="font-semibold"><?= htmlspecialchars($business['owner_name']) ?></p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="p-6 space-y-4">
            <!-- Booking Statistics -->
            <div class="bg-white rounded-2xl p-6 shadow-md">
                <h3 class="font-bold text-lg mb-4">
                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                    Statistik Booking
                </h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-4 rounded-xl text-center stat-card">
                        <div class="text-2xl font-bold text-blue-600"><?= $stats['total_bookings'] ?></div>
                        <div class="text-sm text-blue-800">Total</div>
                    </div>
                    
                    <div class="bg-yellow-50 p-4 rounded-xl text-center stat-card">
                        <div class="text-2xl font-bold text-yellow-600"><?= $stats['scheduled'] ?></div>
                        <div class="text-sm text-yellow-800">Dijadwalkan</div>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-xl text-center stat-card">
                        <div class="text-2xl font-bold text-green-600"><?= $stats['completed'] ?></div>
                        <div class="text-sm text-green-800">Selesai</div>
                    </div>
                    
                    <div class="bg-red-50 p-4 rounded-xl text-center stat-card">
                        <div class="text-2xl font-bold text-red-600"><?= $stats['cancelled'] ?></div>
                        <div class="text-sm text-red-800">Dibatalkan</div>
                    </div>
                </div>
            </div>

            <!-- Payment Statistics -->
            <div class="bg-white rounded-2xl p-6 shadow-md">
                <h3 class="font-bold text-lg mb-4">
                    <i class="fas fa-credit-card text-green-600 mr-2"></i>
                    Status Pembayaran
                </h3>
                
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-red-50 p-4 rounded-xl text-center stat-card">
                        <div class="text-xl font-bold text-red-600"><?= $stats['unpaid'] ?></div>
                        <div class="text-xs text-red-800">Belum Bayar</div>
                    </div>
                    
                    <div class="bg-yellow-50 p-4 rounded-xl text-center stat-card">
                        <div class="text-xl font-bold text-yellow-600"><?= $stats['down_payment'] ?></div>
                        <div class="text-xs text-yellow-800">DP</div>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-xl text-center stat-card">
                        <div class="text-xl font-bold text-green-600"><?= $stats['paid'] ?></div>
                        <div class="text-xs text-green-800">Lunas</div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="bg-white rounded-2xl p-6 shadow-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg">
                        <i class="fas fa-clock text-purple-600 mr-2"></i>
                        Booking Terbaru
                    </h3>
                    <a href="clients.php" class="text-blue-600 text-sm font-semibold">Lihat Semua</a>
                </div>
                
                <?php if ($recent_bookings): ?>
                    <div class="space-y-3">
                        <?php foreach ($recent_bookings as $booking): ?>
                            <div class="border-l-4 <?= $booking['payment_status'] === 'paid' ? 'border-green-500 bg-green-50' : ($booking['payment_status'] === 'down_payment' ? 'border-yellow-500 bg-yellow-50' : 'border-red-500 bg-red-50') ?> p-3 rounded-r-xl">
                                <div class="font-semibold text-sm"><?= htmlspecialchars($booking['client_name']) ?></div>
                                <div class="text-xs text-gray-600"><?= htmlspecialchars($booking['service_name']) ?></div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <?= date('d M Y', strtotime($booking['booking_date'])) ?> • <?= date('H:i', strtotime($booking['start_time'])) ?>
                                </div>
                                <div class="text-xs font-semibold mt-1 <?= $booking['payment_status'] === 'paid' ? 'text-green-600' : ($booking['payment_status'] === 'down_payment' ? 'text-yellow-600' : 'text-red-600') ?>">
                                    <?= $booking['payment_status'] === 'paid' ? 'Lunas' : ($booking['payment_status'] === 'down_payment' ? 'DP' : 'Belum Bayar') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">Belum ada booking</p>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl p-6 shadow-md">
                <h3 class="font-bold text-lg mb-4">
                    <i class="fas fa-bolt text-yellow-600 mr-2"></i>
                    Aksi Cepat
                </h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <a href="../public/?id=<?= $business_id ?>" target="_blank" class="bg-blue-600 text-white p-4 rounded-xl text-center font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-external-link-alt text-xl mb-2 block"></i>
                        <div class="text-sm">Lihat Website</div>
                    </a>
                    
                    <a href="clients.php" class="bg-green-600 text-white p-4 rounded-xl text-center font-semibold hover:bg-green-700 transition">
                        <i class="fas fa-users text-xl mb-2 block"></i>
                        <div class="text-sm">Kelola Client</div>
                    </a>
                    
                    <a href="payments.php" class="bg-purple-600 text-white p-4 rounded-xl text-center font-semibold hover:bg-purple-700 transition">
                        <i class="fas fa-chart-line text-xl mb-2 block"></i>
                        <div class="text-sm">Laporan</div>
                    </a>
                    
                    <a href="profile.php" class="bg-gray-600 text-white p-4 rounded-xl text-center font-semibold hover:bg-gray-700 transition">
                        <i class="fas fa-cog text-xl mb-2 block"></i>
                        <div class="text-sm">Pengaturan</div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Navigation -->
        <?php include 'includes/bottom_nav.php'; ?>
    </div>
</body>
</html>