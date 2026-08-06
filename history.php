<?php require_once __DIR__ . '/../layouts/header.php';
use App\Helpers\Helper;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Transaksi</h4>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><label class="form-label">Dari Tanggal</label><input type="date" name="start_date" class="form-control" value="<?= $filters['start_date'] ?>"></div>
            <div class="col-md-3"><label class="form-label">Sampai Tanggal</label><input type="date" name="end_date" class="form-control" value="<?= $filters['end_date'] ?>"></div>
            <div class="col-md-2"><label class="form-label">Metode</label><select name="metode" class="form-select"><option value="">Semua</option><option value="tunai" <?= $filters['metode']==='tunai'?'selected':'' ?>>Tunai</option><option value="transfer" <?= $filters['metode']==='transfer'?'selected':'' ?>>Transfer</option><option value="qris" <?= $filters['metode']==='qris'?'selected':'' ?>>QRIS</option></select></div>
            <div class="col-md-2"><label class="form-label">&nbsp;</label><button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Invoice</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Diskon</th><th>Grand Total</th><th>Metode</th><th>Kasir</th><th width="80">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada transaksi</td></tr>
                    <?php else: ?>
                    <?php foreach ($transaksi as $t): ?>
                    <tr>
                        <td><strong><?= $t['no_invoice'] ?></strong></td>
                        <td><?= Helper::tanggal($t['created_at']) ?></td>
                        <td><?= $t['nama_pelanggan'] ?: 'Umum' ?></td>
                        <td><?= Helper::rupiah($t['total']) ?></td>
                        <td><?= Helper::rupiah($t['diskon']) ?></td>
                        <td class="fw-bold"><?= Helper::rupiah($t['grand_total']) ?></td>
                        <td><span class="badge bg-secondary"><?= strtoupper($t['metode_pembayaran']) ?></span></td>
                        <td><?= $t['nama_kasir'] ?></td>
                        <td>
                            <a href="<?= \App\Config\App::BASE_URL ?>/transaksi/detail/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="<?= \App\Config\App::BASE_URL ?>/transaksi/cetak/<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak Struk"><i class="bi bi-printer"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>