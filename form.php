<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="bi bi-<?= $is_edit ? 'pencil' : 'plus-circle' ?> me-2"></i>
        <?= $is_edit ? 'Edit Produk' : 'Tambah Produk' ?>
    </h4>
    <a href="<?= \App\Config\App::BASE_URL ?>/produk" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="<?= \App\Config\App::BASE_URL ?>/produk/<?= $is_edit ? 'update/' . $produk['id'] : 'store' ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Kode Produk</label>
                    <input type="text" name="kode" class="form-control" value="<?= $produk['kode'] ?? '' ?>" placeholder="Auto-generated jika kosong">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Barcode</label>
                    <input type="text" name="barcode" class="form-control" value="<?= $produk['barcode'] ?? '' ?>" placeholder="Scan atau masukkan barcode">
                </div>
                <div class="col-12">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" value="<?= $produk['nama'] ?? '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="id_kategori" class="form-select">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategori as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= ($produk['id_kategori'] ?? '') == $k['id'] ? 'selected' : '' ?>><?= $k['nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Supplier</label>
                    <select name="id_supplier" class="form-select">
                        <option value="">Pilih Supplier</option>
                        <?php foreach ($supplier as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($produk['id_supplier'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= $s['nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                    <input type="number" name="harga_beli" class="form-control" value="<?= $produk['harga_beli'] ?? 0 ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                    <input type="number" name="harga_jual" class="form-control" value="<?= $produk['harga_jual'] ?? 0 ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-control" value="<?= $produk['stok'] ?? 0 ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Stok Minimum</label>
                    <input type="number" name="stok_minimum" class="form-control" value="<?= $produk['stok_minimum'] ?? 5 ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gambar</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                    <?php if ($is_edit && $produk['gambar']): ?>
                    <small class="text-muted">Gambar saat ini: <?= $produk['gambar'] ?></small>
                    <br><img src="<?= \App\Config\App::BASE_URL ?>/public/uploads/produk/<?= $produk['gambar'] ?>" class="mt-2" width="80">
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="aktif" class="form-select">
                        <option value="1" <?= ($produk['aktif'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= ($produk['aktif'] ?? 1) == 0 ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> <?= $is_edit ? 'Update Produk' : 'Simpan Produk' ?>
                </button>
                <a href="<?= \App\Config\App::BASE_URL ?>/produk" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>