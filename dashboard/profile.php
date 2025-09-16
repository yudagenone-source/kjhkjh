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

// Get business owner data
$stmt = $conn->prepare("SELECT * FROM business_owners WHERE id = ?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'update_profile':
            $business_name = $db->escape($_POST['business_name']);
            $owner_name = $db->escape($_POST['owner_name']);
            $email = $db->escape($_POST['email']);
            $phone = $db->escape($_POST['phone']);
            $address = $db->escape($_POST['address']);
            
            $stmt = $conn->prepare("UPDATE business_owners SET business_name = ?, owner_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $business_name, $owner_name, $email, $phone, $address, $business_id);
            
            if ($stmt->execute()) {
                $message = 'Profile berhasil diupdate.';
                $messageType = 'success';
                // Refresh data
                $stmt = $conn->prepare("SELECT * FROM business_owners WHERE id = ?");
                $stmt->bind_param("i", $business_id);
                $stmt->execute();
                $business = $stmt->get_result()->fetch_assoc();
            }
            break;
            
        case 'update_midtrans':
            $server_key = $db->escape($_POST['server_key']);
            $client_key = $db->escape($_POST['client_key']);
            
            $stmt = $conn->prepare("UPDATE business_owners SET midtrans_server_key = ?, midtrans_client_key = ? WHERE id = ?");
            $stmt->bind_param("ssi", $server_key, $client_key, $business_id);
            
            if ($stmt->execute()) {
                $message = 'API Key Midtrans berhasil diupdate.';
                $messageType = 'success';
                // Refresh data
                $stmt = $conn->prepare("SELECT * FROM business_owners WHERE id = ?");
                $stmt->bind_param("i", $business_id);
                $stmt->execute();
                $business = $stmt->get_result()->fetch_assoc();
            }
            break;
            
        case 'add_social_link':
            $platform = $db->escape($_POST['platform']);
            $url = $db->escape($_POST['url']);
            $icon = $db->escape($_POST['icon']);
            
            $stmt = $conn->prepare("INSERT INTO social_links (business_owner_id, platform, url, icon) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $business_id, $platform, $url, $icon);
            
            if ($stmt->execute()) {
                $message = 'Link sosial media berhasil ditambahkan.';
                $messageType = 'success';
            }
            break;
            
        case 'delete_social_link':
            $link_id = (int)$_POST['link_id'];
            
            $stmt = $conn->prepare("DELETE FROM social_links WHERE id = ? AND business_owner_id = ?");
            $stmt->bind_param("ii", $link_id, $business_id);
            
            if ($stmt->execute()) {
                $message = 'Link sosial media berhasil dihapus.';
                $messageType = 'success';
            }
            break;
    }
}

// Get social links
$stmt = $conn->prepare("SELECT * FROM social_links WHERE business_owner_id = ? ORDER BY sort_order");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$social_links = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get subscription info
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE business_owner_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$subscription = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - RuangClient</title>
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
        .tab-button {
            transition: all 0.3s ease;
        }
        .tab-button.active {
            background: white;
            color: #4F46E5;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="mobile-app">
        <!-- Header -->
        <div class="gradient-bg text-white p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <a href="index.php" class="text-white mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <h1 class="text-xl font-bold">Profile & Pengaturan</h1>
                </div>
                <a href="logout.php" class="text-red-300 hover:text-red-100">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </a>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white border-b">
            <div class="flex">
                <button onclick="showTab('profile')" class="tab-button flex-1 py-3 px-4 font-semibold text-center active" id="tab-profile">
                    Profile
                </button>
                <button onclick="showTab('midtrans')" class="tab-button flex-1 py-3 px-4 font-semibold text-center text-gray-500" id="tab-midtrans">
                    Payment
                </button>
                <button onclick="showTab('social')" class="tab-button flex-1 py-3 px-4 font-semibold text-center text-gray-500" id="tab-social">
                    Social
                </button>
                <button onclick="showTab('subscription')" class="tab-button flex-1 py-3 px-4 font-semibold text-center text-gray-500" id="tab-subscription">
                    Subscribe
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-2xl <?= $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <!-- Profile Tab -->
            <div id="content-profile" class="tab-content">
                <div class="bg-white rounded-2xl p-6 shadow-md mb-6">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        Informasi Profile
                    </h3>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Bisnis</label>
                                <input type="text" name="business_name" value="<?= htmlspecialchars($business['business_name']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Pemilik</label>
                                <input type="text" name="owner_name" value="<?= htmlspecialchars($business['owner_name']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($business['email']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Telepon</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($business['phone']) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                                <textarea name="address" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($business['address']) ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full mt-6 bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Profile
                        </button>
                    </form>
                </div>

                <!-- Website Link -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-6">
                    <h4 class="font-bold mb-3">
                        <i class="fas fa-link text-blue-600 mr-2"></i>
                        Link Website Anda
                    </h4>
                    <div class="bg-white rounded-xl p-4 mb-4">
                        <div class="text-sm text-gray-600 mb-1">URL Website Client:</div>
                        <div class="font-mono text-blue-600 text-sm break-all">
                            <?= $_SERVER['HTTP_HOST'] ?>/public/?id=<?= $business_id ?>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="copyWebsiteUrl()" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-xl font-semibold hover:bg-blue-700 transition">
                            <i class="fas fa-copy mr-1"></i>
                            Copy Link
                        </button>
                        <a href="../public/?id=<?= $business_id ?>" target="_blank" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-xl font-semibold hover:bg-green-700 transition text-center">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Lihat Website
                        </a>
                    </div>
                </div>
            </div>

            <!-- Midtrans Tab -->
            <div id="content-midtrans" class="tab-content hidden">
                <div class="bg-white rounded-2xl p-6 shadow-md">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-credit-card text-green-600 mr-2"></i>
                        Pengaturan Midtrans
                    </h3>
                    
                    <div class="bg-yellow-50 p-4 rounded-xl mb-6">
                        <div class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            API Key ini digunakan untuk menerima pembayaran dari client Anda.
                            Dapatkan dari <a href="https://dashboard.midtrans.com" target="_blank" class="font-semibold underline">Dashboard Midtrans</a>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_midtrans">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Server Key</label>
                                <input type="text" name="server_key" value="<?= htmlspecialchars($business['midtrans_server_key']) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Client Key</label>
                                <input type="text" name="client_key" value="<?= htmlspecialchars($business['midtrans_client_key']) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full mt-6 bg-green-600 text-white py-3 rounded-xl font-semibold hover:bg-green-700 transition">
                            <i class="fas fa-save mr-2"></i>
                            Simpan API Key
                        </button>
                    </form>
                </div>
            </div>

            <!-- Social Tab -->
            <div id="content-social" class="tab-content hidden">
                <div class="bg-white rounded-2xl p-6 shadow-md mb-6">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-share-alt text-purple-600 mr-2"></i>
                        Link Sosial Media
                    </h3>
                    
                    <form method="POST" class="mb-6">
                        <input type="hidden" name="action" value="add_social_link">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Platform</label>
                                <select name="platform" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updateIcon(this)">
                                    <option value="">Pilih Platform</option>
                                    <option value="WhatsApp" data-icon="fab fa-whatsapp">WhatsApp</option>
                                    <option value="Instagram" data-icon="fab fa-instagram">Instagram</option>
                                    <option value="Facebook" data-icon="fab fa-facebook">Facebook</option>
                                    <option value="Twitter" data-icon="fab fa-twitter">Twitter</option>
                                    <option value="LinkedIn" data-icon="fab fa-linkedin">LinkedIn</option>
                                    <option value="YouTube" data-icon="fab fa-youtube">YouTube</option>
                                    <option value="TikTok" data-icon="fab fa-tiktok">TikTok</option>
                                    <option value="Website" data-icon="fas fa-globe">Website</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">URL</label>
                                <input type="url" name="url" required placeholder="https://" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <input type="hidden" name="icon" id="icon-input">
                        </div>
                        
                        <button type="submit" class="w-full mt-4 bg-purple-600 text-white py-3 rounded-xl font-semibold hover:bg-purple-700 transition">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Link
                        </button>
                    </form>
                </div>

                <!-- Existing Social Links -->
                <?php if ($social_links): ?>
                    <div class="space-y-3">
                        <?php foreach ($social_links as $link): ?>
                            <div class="bg-white rounded-2xl p-4 shadow-md flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="<?= htmlspecialchars($link['icon']) ?> text-xl mr-3 text-gray-600"></i>
                                    <div>
                                        <div class="font-semibold"><?= htmlspecialchars($link['platform']) ?></div>
                                        <div class="text-sm text-gray-500 break-all"><?= htmlspecialchars($link['url']) ?></div>
                                    </div>
                                </div>
                                
                                <form method="POST" class="ml-4">
                                    <input type="hidden" name="action" value="delete_social_link">
                                    <input type="hidden" name="link_id" value="<?= $link['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Hapus link ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Subscription Tab -->
            <div id="content-subscription" class="tab-content hidden">
                <div class="bg-white rounded-2xl p-6 shadow-md">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-star text-yellow-600 mr-2"></i>
                        Status Berlangganan
                    </h3>
                    
                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold text-blue-600 mb-2">Rp 48.000</div>
                        <div class="text-gray-600">per bulan</div>
                        
                        <div class="mt-4 p-4 <?= $business['subscription_status'] === 'active' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' ?> rounded-xl">
                            <div class="font-semibold">Status: <?= ucfirst($business['subscription_status']) ?></div>
                            <?php if ($business['subscription_expires_at']): ?>
                                <div class="text-sm mt-1">
                                    Berakhir: <?= date('d M Y', strtotime($business['subscription_expires_at'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($business['subscription_status'] !== 'active'): ?>
                        <button onclick="paySubscription()" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-2xl font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition">
                            <i class="fas fa-credit-card mr-2"></i>
                            Bayar Berlangganan
                        </button>
                    <?php else: ?>
                        <button onclick="renewSubscription()" class="w-full bg-green-600 text-white py-4 rounded-2xl font-semibold text-lg hover:bg-green-700 transition">
                            <i class="fas fa-redo mr-2"></i>
                            Perpanjang Berlangganan
                        </button>
                    <?php endif; ?>
                    
                    <!-- Subscription History -->
                    <?php if ($subscription): ?>
                        <div class="mt-8">
                            <h4 class="font-bold mb-4">Riwayat Pembayaran</h4>
                            <div class="border-l-4 border-blue-500 pl-4 py-2 bg-blue-50 rounded-r-xl">
                                <div class="font-semibold">Rp <?= number_format($subscription['amount'], 0, ',', '.') ?></div>
                                <div class="text-sm text-gray-600">
                                    <?= date('d M Y H:i', strtotime($subscription['created_at'])) ?>
                                </div>
                                <div class="text-sm font-semibold <?= $subscription['payment_status'] === 'paid' ? 'text-green-600' : 'text-red-600' ?>">
                                    Status: <?= ucfirst($subscription['payment_status']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bottom Navigation -->
        <?php include 'includes/bottom_nav.php'; ?>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
                button.classList.add('text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Add active class to selected tab button
            const activeButton = document.getElementById('tab-' + tabName);
            activeButton.classList.add('active');
            activeButton.classList.remove('text-gray-500');
        }

        function updateIcon(select) {
            const selectedOption = select.options[select.selectedIndex];
            const icon = selectedOption.getAttribute('data-icon');
            document.getElementById('icon-input').value = icon || '';
        }

        function copyWebsiteUrl() {
            const url = `${window.location.protocol}//${window.location.host}/public/?id=<?= $business_id ?>`;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link website berhasil disalin!');
            });
        }

        function paySubscription() {
            alert('Fitur pembayaran berlangganan akan segera tersedia!');
        }

        function renewSubscription() {
            alert('Fitur perpanjangan berlangganan akan segera tersedia!');
        }
    </script>
</body>
</html>