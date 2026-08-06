<?php
use App\Core\Session;

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$role = Session::get('user_role');
?>
<nav id="sidebar-wrapper" class="bg-white border-end sidebar">
    <div class="list-group list-group-flush">
        <a href="<?= \App\Config\App::BASE_URL ?>/dashboard"
           class="list-group-item list-group-item-action <?= in_array($currentPath, ['/', '/dashboard']) ? 'active' : '' ?>">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="<?= \App\Config\App::BASE_URL ?>/produk"
           class="list-group-item list-group-item-action <?= strpos($currentPath, '/produk') === 0 ? 'active' : '' ?>">
            <i class="bi bi-box me-2"></i> Produk
        </a>
        <a href="<?= \App\Config\App::BASE_URL ?>/kategori"
           class="list-group-item list-group-item-action <?= strpos($currentPath, '/kategori') === 0 ? 'active' : '' ?>">
            <i class="bi bi-tags me-2"></i> Kategori
        </a>
        <a href="<?= \App\Config\App::BASE_URL ?>/supplier"
           class="list-group-item list-group-item-action <?= strpos($currentPath, '/supplier') === 0 ? 'active' : '' ?>">
            <i class="bi bi-truck me-2"></i> Supplier
        </a>
        <a href="<?= \App\Config\App::BASE_URL ?>/pelanggan"
           class="list-group-item list-group-item-action <?= strpos($currentPath, '/pelanggan') === 0 ? 'active' : '' ?>">
            <i class="bi bi-people me-2"></i> Pelanggan
        </a>
        <a href="<?= \App\Config\App::BASE_URL ?>/transaksi/kasir"
           class="list-group-item list-group-item-action <?= strpos($currentPath, '/transaksi/kasir') === 0 ? 'active' : '' ?>">
            <i class="bi bi-cart3 me-2"></i> Kasir
        </a>
        <a href="<?= \App\Config\App::BASE_URL ?>/transaksi/history"
           class="list-group-item list-group-item-action <?= strpos($currentPath, '/transaksi/history') === 0 ? 'active' : '' ?>">
            <i class="bi bi-clock-history me-2"></i> Riwayat
        </a>
        <?php if (in_array($role, ['admin', 'owner'])): ?>
        <a href="<?= \App\Config\App::BASE_URL ?>/laporan"
           class="list-group-item list-group-item-action <?= strpos($currentPath, '/laporan') === 0 ? 'active' : '' ?>">
            <i class="bi bi-bar-chart me-2"></i> Laporan
        </a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
        <a href="<?= \App\Config\App::BASE_URL ?>/pengaturan"
           class="list-group-item list-group-item-action <?= strpos($currentPath, '/pengaturan') === 0 ? 'active' : '' ?>">
            <i class="bi bi-gear me-2"></i> Pengaturan
        </a>
        <?php endif; ?>
    </div>
</nav>