<?php
/**
 * Produk List View - Halaman Daftar Produk
 */
?>
<?php $this->render('Layout/header'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Manajemen Produk</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo BASE_URL; ?>produk/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
        </div>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?php echo BASE_URL; ?>produk" class="form-inline">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Cari produk..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <select name="category" class="form-control mr-2">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories ?? [] as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>" 
                                    <?php echo ($_GET['category'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-secondary">Cari</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Produk</th>
                                <th>SKU</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($produk) && count($produk) > 0): ?>
                                <?php foreach ($produk as $idx => $p): ?>
                                    <tr>
                                        <td><?php echo $idx + 1; ?></td>
                                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td><?php echo htmlspecialchars($p['sku']); ?></td>
                                        <td><?php echo htmlspecialchars($p['category']); ?></td>
                                        <td>Rp <?php echo number_format($p['price'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $p['stock'] > 10 ? 'badge-success' : ($p['stock'] > 0 ? 'badge-warning' : 'badge-danger'); ?>">
                                                <?php echo $p['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $p['status'] == 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                                                <?php echo ucfirst($p['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>produk/<?php echo $p['id']; ?>/edit" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="<?php echo BASE_URL; ?>produk/<?php echo $p['id']; ?>/delete" style="display:inline;">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada produk</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->render('Layout/footer'); ?>
