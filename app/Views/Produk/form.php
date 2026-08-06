<?php
/**
 * Produk Create/Edit View
 */
?>
<?php $this->render('Layout/header'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><?php echo isset($produk) ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h1>
        </div>
    </div>
    
    <?php if (isset($errors) && count($errors) > 0): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo isset($produk) ? BASE_URL . 'produk/' . $produk['id'] . '/update' : BASE_URL . 'produk/store'; ?>" enctype="multipart/form-data">
                        
                        <div class="form-group">
                            <label for="name">Nama Produk *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($produk['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="sku">SKU/Kode Produk *</label>
                            <input type="text" id="sku" name="sku" class="form-control" 
                                   value="<?php echo htmlspecialchars($produk['sku'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Kategori *</label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories ?? [] as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['id']); ?>" 
                                        <?php echo ($produk['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="price">Harga Jual *</label>
                                <input type="number" id="price" name="price" class="form-control" 
                                       value="<?php echo htmlspecialchars($produk['price'] ?? ''); ?>" 
                                       step="0.01" min="0" required>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="cost">Harga Pokok</label>
                                <input type="number" id="cost" name="cost" class="form-control" 
                                       value="<?php echo htmlspecialchars($produk['cost'] ?? ''); ?>" 
                                       step="0.01" min="0">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="stock">Stok *</label>
                                <input type="number" id="stock" name="stock" class="form-control" 
                                       value="<?php echo htmlspecialchars($produk['stock'] ?? '0'); ?>" 
                                       min="0" required>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="min_stock">Stok Minimum</label>
                                <input type="number" id="min_stock" name="min_stock" class="form-control" 
                                       value="<?php echo htmlspecialchars($produk['min_stock'] ?? '0'); ?>" min="0">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($produk['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="active" <?php echo ($produk['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo ($produk['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <a href="<?php echo BASE_URL; ?>produk" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->render('Layout/footer'); ?>
