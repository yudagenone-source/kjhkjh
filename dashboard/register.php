<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['business_owner_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);
    $business_name = $_POST['business_name'];
    $owner_name = $_POST['owner_name'];
    $phone = $_POST['phone'];
    
    if ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak sesuai.';
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM business_owners WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username atau email sudah digunakan.';
        } else {
            // Insert new business owner
            $stmt = $conn->prepare("INSERT INTO business_owners (username, email, password, business_name, owner_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, $password, $business_name, $owner_name, $phone);
            
            if ($stmt->execute()) {
                $business_id = $conn->insert_id;
                
                // Create subscription payment
                $subscription_amount = 48000; // Default subscription price
                $order_id = 'SUBSCRIPTION-' . $business_id . '-' . time();
                
                $stmt = $conn->prepare("INSERT INTO subscriptions (business_owner_id, amount, midtrans_order_id) VALUES (?, ?, ?)");
                $stmt->bind_param("ids", $business_id, $subscription_amount, $order_id);
                $stmt->execute();
                
                $success = 'Registrasi berhasil! Silahkan login dan lakukan pembayaran berlangganan.';
            } else {
                $error = 'Terjadi kesalahan saat registrasi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - RuangClient</title>
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
        <div class="gradient-bg text-white text-center py-12 px-6">
            <i class="fas fa-user-plus text-4xl mb-4"></i>
            <h1 class="text-xl font-bold mb-2">Daftar RuangClient</h1>
            <p class="text-sm opacity-90">Mulai kelola bisnis Anda dengan mudah</p>
        </div>

        <!-- Registration Form -->
        <div class="p-6">
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-2xl">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-2xl">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-md">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-building text-blue-600 mr-2"></i>
                        Informasi Bisnis
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Bisnis</label>
                            <input type="text" name="business_name" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Pemilik</label>
                            <input type="text" name="owner_name" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Telepon</label>
                            <input type="tel" name="phone" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-md">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        Akun Login
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-2xl p-6">
                    <div class="text-center">
                        <i class="fas fa-credit-card text-blue-600 text-3xl mb-3"></i>
                        <h4 class="font-bold text-lg text-blue-800 mb-2">Biaya Berlangganan</h4>
                        <div class="text-3xl font-bold text-blue-600 mb-2">Rp 48.000</div>
                        <div class="text-sm text-blue-700 mb-4">per bulan</div>
                        <ul class="text-sm text-blue-700 text-left space-y-1">
                            <li><i class="fas fa-check mr-2"></i>Website Client Unlimited</li>
                            <li><i class="fas fa-check mr-2"></i>Dashboard Lengkap</li>
                            <li><i class="fas fa-check mr-2"></i>Payment Gateway</li>
                            <li><i class="fas fa-check mr-2"></i>Support 24/7</li>
                        </ul>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 px-6 rounded-2xl font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition duration-300">
                    <i class="fas fa-user-plus mr-2"></i>
                    Daftar & Berlangganan
                </button>
            </form>

            <div class="text-center mt-6">
                <p class="text-gray-600">Sudah punya akun?</p>
                <a href="login.php" class="text-blue-600 font-semibold hover:text-blue-700">
                    Masuk ke Dashboard
                </a>
            </div>

            <div class="text-center mt-8">
                <a href="../" class="text-gray-500 hover:text-gray-700 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>