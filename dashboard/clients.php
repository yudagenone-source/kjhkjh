<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['business_owner_id'])) {
    header('Location: login.php');
    exit;
}

$business_id = $_SESSION['business_owner_id'];
$db = new Database();
$conn = $db->getConnection();

// Handle CRUD operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'update_status':
            $booking_id = (int)$_POST['booking_id'];
            $status = $db->escape($_POST['status']);
            
            $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ? AND business_owner_id = ?");
            $stmt->bind_param("sii", $status, $booking_id, $business_id);
            
            if ($stmt->execute()) {
                $message = 'Status booking berhasil diupdate.';
                $messageType = 'success';
            }
            break;
            
        case 'update_payment':
            $booking_id = (int)$_POST['booking_id'];
            $payment_status = $db->escape($_POST['payment_status']);
            
            $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE id = ? AND business_owner_id = ?");
            $stmt->bind_param("sii", $payment_status, $booking_id, $business_id);
            
            if ($stmt->execute()) {
                $message = 'Status pembayaran berhasil diupdate.';
                $messageType = 'success';
            }
            break;
            
        case 'delete_booking':
            $booking_id = (int)$_POST['booking_id'];
            
            $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND business_owner_id = ?");
            $stmt->bind_param("ii", $booking_id, $business_id);
            
            if ($stmt->execute()) {
                $message = 'Booking berhasil dihapus.';
                $messageType = 'success';
            }
            break;
    }
}

// Get all bookings with client and service details
$stmt = $conn->prepare("
    SELECT b.*, c.name as client_name, c.whatsapp, c.email, s.service_name 
    FROM bookings b
    JOIN clients c ON b.client_id = c.id
    JOIN services s ON b.service_id = s.id
    WHERE b.business_owner_id = ?
    ORDER BY b.booking_date DESC, b.start_time DESC
");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Client - RuangClient</title>
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="mobile-app">
        <!-- Header -->
        <div class="gradient-bg text-white p-6">
            <div class="flex items-center mb-4">
                <a href="index.php" class="text-white mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-xl font-bold">Kelola Client</h1>
            </div>
            <p class="text-sm opacity-75">Manajemen booking dan client</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-2xl <?= $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex space-x-3 mb-6">
                <a href="calendar.php" class="flex-1 bg-blue-600 text-white text-center py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Calendar
                </a>
                <a href="services.php" class="flex-1 bg-green-600 text-white text-center py-3 px-4 rounded-xl font-semibold hover:bg-green-700 transition">
                    <i class="fas fa-cog mr-1"></i>
                    Layanan
                </a>
            </div>

            <!-- Bookings List -->
            <?php if ($bookings): ?>
                <div class="space-y-4">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="bg-white rounded-2xl p-4 shadow-md">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-bold text-lg"><?= htmlspecialchars($booking['client_name']) ?></h4>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($booking['service_name']) ?></p>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="showEditModal(<?= $booking['id'] ?>, '<?= $booking['status'] ?>', '<?= $booking['payment_status'] ?>')" class="text-blue-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteBooking(<?= $booking['id'] ?>)" class="text-red-600">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone mr-2 w-4"></i>
                                    <a href="https://wa.me/<?= ltrim($booking['whatsapp'], '0') ?>" target="_blank" class="text-blue-600 hover:underline">
                                        <?= htmlspecialchars($booking['whatsapp']) ?>
                                    </a>
                                </div>
                                
                                <?php if ($booking['email']): ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-envelope mr-2 w-4"></i>
                                        <?= htmlspecialchars($booking['email']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-calendar mr-2 w-4"></i>
                                    <?= date('d M Y', strtotime($booking['booking_date'])) ?> • <?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?>
                                </div>
                                
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-money-bill mr-2 w-4"></i>
                                    Rp <?= number_format($booking['total_amount'], 0, ',', '.') ?>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center mt-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= 
                                    $booking['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                    ($booking['status'] === 'confirmed' ? 'bg-blue-100 text-blue-800' : 
                                    ($booking['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) 
                                ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                                
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= 
                                    $booking['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                    ($booking['payment_status'] === 'down_payment' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') 
                                ?>">
                                    <?= $booking['payment_status'] === 'paid' ? 'Lunas' : ($booking['payment_status'] === 'down_payment' ? 'DP' : 'Belum Bayar') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">Belum ada booking client</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bottom Navigation -->
        <?php include 'includes/bottom_nav.php'; ?>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-6">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md">
                <h3 class="font-bold text-lg mb-4">Edit Booking</h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="">
                    <input type="hidden" name="booking_id" value="">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Booking</label>
                            <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Pembayaran</label>
                            <select name="payment_status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="unpaid">Belum Bayar</option>
                                <option value="down_payment">Down Payment</option>
                                <option value="paid">Lunas</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-6">
                        <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-xl font-semibold">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-semibold">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showEditModal(bookingId, status, paymentStatus) {
            const modal = document.getElementById('editModal');
            const form = modal.querySelector('form');
            
            form.querySelector('input[name="action"]').value = 'update_status';
            form.querySelector('input[name="booking_id"]').value = bookingId;
            form.querySelector('select[name="status"]').value = status;
            form.querySelector('select[name="payment_status"]').value = paymentStatus;
            
            modal.classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteBooking(bookingId) {
            if (confirm('Apakah Anda yakin ingin menghapus booking ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_booking">
                    <input type="hidden" name="booking_id" value="${bookingId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>