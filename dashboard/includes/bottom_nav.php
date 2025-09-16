<!-- Bottom Navigation -->
<div class="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[400px] bg-white shadow-lg border-t border-gray-200">
    <div class="flex justify-around py-2">
        <a href="index.php" class="flex flex-col items-center p-3 text-xs <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'text-blue-600' : 'text-gray-500' ?>">
            <i class="fas fa-home text-lg mb-1"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="clients.php" class="flex flex-col items-center p-3 text-xs <?= basename($_SERVER['PHP_SELF']) === 'clients.php' ? 'text-blue-600' : 'text-gray-500' ?>">
            <i class="fas fa-users text-lg mb-1"></i>
            <span>Client</span>
        </a>
        
        <a href="calendar.php" class="flex flex-col items-center p-3 text-xs <?= basename($_SERVER['PHP_SELF']) === 'calendar.php' ? 'text-blue-600' : 'text-gray-500' ?>">
            <i class="fas fa-calendar-alt text-lg mb-1"></i>
            <span>Calendar</span>
        </a>
        
        <a href="payments.php" class="flex flex-col items-center p-3 text-xs <?= basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'text-blue-600' : 'text-gray-500' ?>">
            <i class="fas fa-chart-line text-lg mb-1"></i>
            <span>Laporan</span>
        </a>
        
        <a href="profile.php" class="flex flex-col items-center p-3 text-xs <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'text-blue-600' : 'text-gray-500' ?>">
            <i class="fas fa-user text-lg mb-1"></i>
            <span>Profile</span>
        </a>
    </div>
</div>

<!-- Bottom padding to prevent content being hidden by bottom nav -->
<div class="pb-20"></div>