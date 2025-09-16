<?php
require_once '../config/database.php';
require_once '../config/midtrans.php';

$business_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$db = new Database();
$conn = $db->getConnection();

// Get business owner data
$stmt = $conn->prepare("SELECT * FROM business_owners WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();

if (!$business) {
    header('Location: ../');
    exit;
}

// Get services
$stmt = $conn->prepare("SELECT * FROM services WHERE business_owner_id = ? AND is_active = 1");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $db->escape($_POST['name']);
    $email = $db->escape($_POST['email']);
    $whatsapp = $db->escape($_POST['whatsapp']);
    $service_id = (int)$_POST['service_id'];
    $booking_date = $db->escape($_POST['booking_date']);
    $start_time = $db->escape($_POST['start_time']);
    
    // Get service details
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ? AND business_owner_id = ?");
    $stmt->bind_param("ii", $service_id, $business_id);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();
    
    if ($service) {
        // Calculate end time
        $start_datetime = DateTime::createFromFormat('H:i', $start_time);
        $end_datetime = clone $start_datetime;
        $end_datetime->add(new DateInterval('PT' . $service['duration'] . 'M'));
        $end_time = $end_datetime->format('H:i');
        
        // Check if client exists
        $stmt = $conn->prepare("SELECT id FROM clients WHERE whatsapp = ? AND business_owner_id = ?");
        $stmt->bind_param("si", $whatsapp, $business_id);
        $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();
        
        if (!$client) {
            // Create new client
            $stmt = $conn->prepare("INSERT INTO clients (business_owner_id, name, email, whatsapp) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $business_id, $name, $email, $whatsapp);
            $stmt->execute();
            $client_id = $conn->insert_id;
        } else {
            $client_id = $client['id'];
        }
        
        // Create booking
        $order_id = 'BOOKING-' . $business_id . '-' . time();
        $stmt = $conn->prepare("INSERT INTO bookings (business_owner_id, client_id, service_id, booking_date, start_time, end_time, total_amount, midtrans_order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisssds", $business_id, $client_id, $service_id, $booking_date, $start_time, $end_time, $service['price'], $order_id);
        
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            
            // Initialize Midtrans if API key exists
            if ($business['midtrans_server_key']) {
                MidtransConfig::init($business['midtrans_server_key']);
                
                $params = array(
                    'transaction_details' => array(
                        'order_id' => $order_id,
                        'gross_amount' => $service['price'],
                    ),
                    'customer_details' => array(
                        'first_name' => $name,
                        'email' => $email,
                        'phone' => $whatsapp,
                    ),
                    'item_details' => array(
                        array(
                            'id' => 'service-' . $service_id,
                            'price' => $service['price'],
                            'quantity' => 1,
                            'name' => $service['service_name']
                        )
                    )
                );
                
                $snapToken = MidtransConfig::createTransaction($params);
                
                if ($snapToken) {
                    // Redirect to payment
                    header("Location: payment.php?token=" . $snapToken . "&booking=" . $booking_id);
                    exit;
                }
            }
            
            $message = 'Booking berhasil dibuat! Silahkan hubungi kami untuk konfirmasi pembayaran.';
            $messageType = 'success';
        } else {
            $message = 'Terjadi kesalahan saat membuat booking.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - <?= htmlspecialchars($business['business_name']) ?></title>
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
                <a href="index.php?id=<?= $business_id ?>" class="text-white mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-xl font-bold">Booking Layanan</h1>
            </div>
            <p class="text-sm opacity-75"><?= htmlspecialchars($business['business_name']) ?></p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-2xl <?= $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-md">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        Informasi Client
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                            <input type="text" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email (Opsional)</label>
                            <input type="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor WhatsApp *</label>
                            <input type="tel" name="whatsapp" required placeholder="08xxxxxxxxxx" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-md">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                        Detail Booking
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Layanan *</label>
                            <select name="service_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="updatePrice(this)">
                                <option value="">Pilih Layanan</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id'] ?>" data-price="<?= $service['price'] ?>" data-duration="<?= $service['duration'] ?>">
                                        <?= htmlspecialchars($service['service_name']) ?> - Rp <?= number_format($service['price'], 0, ',', '.') ?> (<?= $service['duration'] ?> menit)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Booking *</label>
                            <input type="date" name="booking_date" required min="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Waktu Mulai *</label>
                            <input type="time" name="start_time" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div id="price-info" class="hidden bg-blue-50 p-4 rounded-xl">
                            <div class="text-sm text-blue-800 font-semibold">Total Harga:</div>
                            <div id="total-price" class="text-2xl font-bold text-blue-600"></div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 px-6 rounded-2xl font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition duration-300">
                    <i class="fas fa-calendar-check mr-2"></i>
                    Buat Booking & Bayar
                </button>
            </form>
        </div>
    </div>

    <script>
        function updatePrice(select) {
            const selectedOption = select.options[select.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            
            if (price) {
                document.getElementById('price-info').classList.remove('hidden');
                document.getElementById('total-price').textContent = 'Rp ' + parseInt(price).toLocaleString('id-ID');
            } else {
                document.getElementById('price-info').classList.add('hidden');
            }
        }
    </script>
</body>
</html>