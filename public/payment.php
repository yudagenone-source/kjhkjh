<?php
require_once '../config/database.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$booking_id = isset($_GET['booking']) ? (int)$_GET['booking'] : 0;

if (!$token || !$booking_id) {
    header('Location: ../');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, s.service_name, s.price, c.name as client_name, bo.business_name, bo.midtrans_client_key
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN clients c ON b.client_id = c.id
    JOIN business_owners bo ON b.business_owner_id = bo.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header('Location: ../');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - <?= htmlspecialchars($booking['business_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= htmlspecialchars($booking['midtrans_client_key']) ?>"></script>
    <style>
        .mobile-app {
            max-width: 400px;
            margin: 0 auto;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="mobile-app">
        <!-- Header -->
        <div class="gradient-bg text-white p-6">
            <h1 class="text-xl font-bold">Pembayaran</h1>
            <p class="text-sm opacity-75"><?= htmlspecialchars($booking['business_name']) ?></p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <div class="bg-white rounded-2xl p-6 shadow-md mb-6">
                <h3 class="font-bold text-lg mb-4">
                    <i class="fas fa-receipt text-blue-600 mr-2"></i>
                    Detail Booking
                </h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nama Client:</span>
                        <span class="font-semibold"><?= htmlspecialchars($booking['client_name']) ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Layanan:</span>
                        <span class="font-semibold"><?= htmlspecialchars($booking['service_name']) ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal:</span>
                        <span class="font-semibold"><?= date('d M Y', strtotime($booking['booking_date'])) ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Waktu:</span>
                        <span class="font-semibold"><?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?></span>
                    </div>
                    
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between text-lg">
                            <span class="font-bold">Total:</span>
                            <span class="font-bold text-blue-600">Rp <?= number_format($booking['price'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <button onclick="payWithMidtrans()" class="w-full bg-gradient-to-r from-green-600 to-blue-600 text-white py-4 px-6 rounded-2xl font-semibold text-lg hover:from-green-700 hover:to-blue-700 transition duration-300">
                <i class="fas fa-credit-card mr-2"></i>
                Bayar Sekarang
            </button>
            
            <div class="text-center mt-6">
                <a href="index.php?id=<?= $booking['business_owner_id'] ?>" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script>
        function payWithMidtrans() {
            snap.pay('<?= $token ?>', {
                onSuccess: function(result) {
                    alert('Pembayaran berhasil!');
                    window.location.href = 'success.php?booking=<?= $booking_id ?>';
                },
                onPending: function(result) {
                    alert('Pembayaran pending, silahkan selesaikan pembayaran.');
                },
                onError: function(result) {
                    alert('Pembayaran gagal!');
                },
                onClose: function() {
                    alert('Anda menutup popup pembayaran tanpa menyelesaikan pembayaran');
                }
            });
        }
    </script>
</body>
</html>