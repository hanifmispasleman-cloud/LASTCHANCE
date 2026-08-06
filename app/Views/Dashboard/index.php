<?php
/**
 * Dashboard View - Halaman Dashboard Utama
 */
?>
<?php $this->render('Layout/header'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Dashboard</h1>
            <p class="text-muted">Selamat datang kembali, <?php echo htmlspecialchars($user['name']); ?></p>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-title">Total Penjualan</h6>
                    <h2 class="stat-value">Rp <?php echo number_format($stats['total_sales'] ?? 0, 0, ',', '.'); ?></h2>
                    <p class="text-muted small">Bulan ini</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-title">Total Transaksi</h6>
                    <h2 class="stat-value"><?php echo $stats['total_transactions'] ?? 0; ?></h2>
                    <p class="text-muted small">Bulan ini</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-title">Total Produk</h6>
                    <h2 class="stat-value"><?php echo $stats['total_products'] ?? 0; ?></h2>
                    <p class="text-muted small">Tersedia</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-title">Stok Rendah</h6>
                    <h2 class="stat-value" style="color: #ff6b6b;"><?php echo $stats['low_stock'] ?? 0; ?></h2>
                    <p class="text-muted small">Perlu diisi ulang</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Grafik Penjualan (7 Hari Terakhir)</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Kategori Terlaris</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Transactions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Transaksi Terbaru</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>No Transaksi</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($recent_transactions) && count($recent_transactions) > 0): ?>
                                <?php foreach ($recent_transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                                        <td>Rp <?php echo number_format($transaction['total'], 0, ',', '.'); ?></td>
                                        <td><span class="badge badge-success">Selesai</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada transaksi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/chart.js"></script>
<script>
    // Initialize charts
    var salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels ?? []); ?>,
            datasets: [{
                label: 'Penjualan',
                data: <?php echo json_encode($chart_data ?? []); ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php $this->render('Layout/footer'); ?>
