<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['business_owner_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM business_owners WHERE (username = ? OR email = ?) AND password = ?");
    $stmt->bind_param("sss", $username, $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['is_active']) {
            $_SESSION['business_owner_id'] = $user['id'];
            $_SESSION['business_name'] = $user['business_name'];
            $_SESSION['owner_name'] = $user['owner_name'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Akun Anda tidak aktif. Silahkan hubungi admin.';
        }
    } else {
        $error = 'Username/email atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RuangClient</title>
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
        <div class="gradient-bg text-white text-center py-16 px-6">
            <i class="fas fa-users text-5xl mb-4"></i>
            <h1 class="text-2xl font-bold mb-2">RuangClient</h1>
            <p class="text-lg opacity-90">Dashboard Pemilik Usaha</p>
        </div>

        <!-- Login Form -->
        <div class="p-6">
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-2xl">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-md">
                    <h3 class="font-bold text-lg mb-6 text-center">
                        <i class="fas fa-sign-in-alt text-blue-600 mr-2"></i>
                        Masuk ke Dashboard
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Username atau Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" name="username" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" name="password" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 px-6 rounded-2xl font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Masuk Dashboard
                </button>
            </form>

            <div class="text-center mt-6 space-y-4">
                <p class="text-gray-600">Belum punya akun?</p>
                <a href="register.php" class="inline-block w-full bg-white text-gray-800 py-3 px-6 rounded-2xl font-semibold border-2 border-gray-300 hover:border-blue-500 hover:text-blue-600 transition duration-300">
                    <i class="fas fa-user-plus mr-2"></i>
                    Daftar Sekarang
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