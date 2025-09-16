<?php
require_once '../config/database.php';

// Get business info from URL parameter or use default
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

// Get social links
$stmt = $conn->prepare("SELECT * FROM social_links WHERE business_owner_id = ? AND is_active = 1 ORDER BY sort_order");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$social_links = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get services
$stmt = $conn->prepare("SELECT * FROM services WHERE business_owner_id = ? AND is_active = 1");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($business['business_name']) ?> - RuangClient</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .mobile-app {
            max-width: 400px;
            margin: 0 auto;
        }
        .link-button {
            transition: all 0.3s ease;
        }
        .link-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="mobile-app">
        <!-- Header -->
        <div class="gradient-bg text-white text-center py-12 px-6 rounded-b-3xl">
            <?php if ($business['profile_image']): ?>
                <img src="<?= htmlspecialchars($business['profile_image']) ?>" alt="Profile" class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-white shadow-lg">
            <?php else: ?>
                <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-user text-3xl"></i>
                </div>
            <?php endif; ?>
            
            <h1 class="text-2xl font-bold mb-2"><?= htmlspecialchars($business['business_name']) ?></h1>
            <p class="text-lg opacity-90"><?= htmlspecialchars($business['owner_name']) ?></p>
            <?php if ($business['phone']): ?>
                <p class="text-sm opacity-75 mt-1">
                    <i class="fas fa-phone mr-1"></i>
                    <?= htmlspecialchars($business['phone']) ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4">
            <!-- Booking Button -->
            <a href="booking.php?id=<?= $business_id ?>" class="block w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white text-center py-4 px-6 rounded-2xl font-semibold text-lg link-button">
                <i class="fas fa-calendar-check mr-2"></i>
                Booking Layanan
            </a>

            <!-- Social Links -->
            <?php foreach ($social_links as $link): ?>
                <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="block w-full bg-white text-gray-800 text-center py-4 px-6 rounded-2xl font-semibold shadow-md link-button">
                    <i class="<?= htmlspecialchars($link['icon']) ?> mr-2 text-xl"></i>
                    <?= ucfirst(htmlspecialchars($link['platform'])) ?>
                </a>
            <?php endforeach; ?>

            <!-- Services Info -->
            <?php if ($services): ?>
                <div class="bg-white rounded-2xl p-6 shadow-md mt-8">
                    <h3 class="text-lg font-bold mb-4 text-center">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        Layanan Kami
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($services as $service): ?>
                            <div class="border-l-4 border-blue-500 pl-4 py-2">
                                <div class="font-semibold"><?= htmlspecialchars($service['service_name']) ?></div>
                                <div class="text-sm text-gray-600"><?= htmlspecialchars($service['description']) ?></div>
                                <div class="text-lg font-bold text-blue-600 mt-1">
                                    Rp <?= number_format($service['price'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="text-center text-gray-500 text-sm mt-8 py-4">
                <p>Powered by <span class="font-semibold text-blue-600">RuangClient</span></p>
            </div>
        </div>
    </div>
</body>
</html>