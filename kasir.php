<?php require_once __DIR__ . '/../layouts/header.php';
use App\Helpers\Helper;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-cart3 me-2"></i>Kasir</h4>
    <span class="text-muted" id="currentTime"><?= date('d/m/Y H:i:s') ?></span>
</div>

<div class="row g-3">
    <!-- Panel Kiri: Pencarian & Keranjang -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchProduk" class="form-control form-control-lg" placeholder="Cari produk..." autofocus>
                        </div>
                        <div id="searchResults" class="list-group position-absolute w-100 shadow" style="z-index:1000;max-height:300px;overflow-y:auto;display:none;"></div>
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="barcodeInput" class="form-control form-control-lg" placeholder="Scan Barcode">
                    </div>
                    <div class="col-md-3">
                        <select id="selectPelanggan" class="form-select form-select-lg">
                            <option value="">Umum</option>
                            <?php foreach ($pelanggan as $pl): ?>
                            <option value="<?= $pl['id'] ?>"><?= $pl['nama'] ?> (<?= $pl['telepon'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-basket me-2"></i>Keranjang Belanja</h6>
                <button class="btn btn-outline-danger btn-sm" onclick="clearCart()"><i class="bi bi-trash"></i> Kosongkan</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="cartTable">
                        <thead class="table-light">
                            <tr><th>Produk</th><th width="100">Harga</th><th width="80">Qty</th><th width="120">Subtotal</th><th width="50"></th></tr>
                        </thead>
                        <tbody id="cartBody">
                            <tr id="emptyCart"><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-cart-x display-4"></i><p class="mt-2">Keranjang masih kosong</p></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Kanan -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
            <div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan</h6></div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label fw-semibold">Subtotal</label><h4 class="text-end mb-0" id="displaySubtotal">Rp 0</h4></div>
                <div class="row g-2 mb-3">
                    <div class="col-6"><label class="form-label">Diskon (Rp)</label><input type="number" id="inputDiskon" class="form-control" value="0" min="0" onchange="hitungTotal()"></div>
                    <div class="col-6"><label class="form-label">Pajak (Rp)</label><input type="number" id="inputPajak" class="form-control" value="0" min="0" onchange="hitungTotal()"></div>
                </div>
                <hr>
                <div class="mb-3"><label class="form-label fw-semibold">Grand Total</label><h3 class="text-end text-primary mb-0" id="displayGrandTotal">Rp 0</h3></div>
                <div class="mb-3"><label class="form-label">Metode Pembayaran</label><select id="metodeBayar" class="form-select" onchange="toggleBayar()"><option value="tunai">Tunai</option><option value="transfer">Transfer</option><option value="qris">QRIS</option></select></div>
                <div class="mb-3" id="divBayar"><label class="form-label">Bayar</label><input type="number" id="inputBayar" class="form-control form-control-lg" placeholder="Jumlah bayar" onkeyup="hitungKembalian()"></div>
                <div class="mb-3" id="divKembalian" style="display:none;"><label class="form-label fw-semibold text-success">Kembalian</label><h4 class="text-success mb-0" id="displayKembalian">Rp 0</h4></div>
                <div class="mb-3"><label class="form-label">Catatan</label><textarea id="inputCatatan" class="form-control" rows="2"></textarea></div>
                <button class="btn btn-primary btn-lg w-100" onclick="prosesTransaksi()" id="btnProses"><i class="bi bi-check-circle"></i> Proses Pembayaran</button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="csrfToken" value="<?= $csrf_token ?>">
<input type="hidden" id="baseUrl" value="<?= \App\Config\App::BASE_URL ?>">

<script src="<?= \App\Config\App::BASE_URL ?>/public/js/kasir.js"></script>
<script>setInterval(() => document.getElementById('currentTime').textContent = new Date().toLocaleString('id-ID'), 1000);</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>