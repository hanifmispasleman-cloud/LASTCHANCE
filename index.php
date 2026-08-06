<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-truck me-2"></i>Supplier</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSupplier" onclick="resetForm()">
        <i class="bi bi-plus-lg"></i> Tambah Supplier
    </button>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="keyword" class="form-control" placeholder="Cari supplier..." value="<?= $keyword ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Cari</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($supplier)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data supplier</td></tr>
                    <?php else: ?>
                    <?php foreach ($supplier as $s): ?>
                    <tr>
                        <td class="fw-semibold"><?= $s['nama'] ?></td>
                        <td><?= $s['telepon'] ?: '-' ?></td>
                        <td><?= $s['email'] ?: '-' ?></td>
                        <td><?= $s['alamat'] ?: '-' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editSupplier(<?= $s['id'] ?>, '<?= addslashes($s['nama']) ?>', '<?= addslashes($s['telepon'] ?? '') ?>', '<?= addslashes($s['email'] ?? '') ?>', '<?= addslashes($s['alamat'] ?? '') ?>')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= \App\Config\App::BASE_URL ?>/supplier/delete/<?= $s['id'] ?>', '<?= addslashes($s['nama']) ?>', '<?= $csrf_token ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Supplier -->
<div class="modal fade" id="modalSupplier" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formSupplier" method="POST" action="<?= \App\Config\App::BASE_URL ?>/supplier/store">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nama <span class="text-danger">*</span></label><input type="text" name="nama" id="supNama" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Telepon</label><input type="text" name="telepon" id="supTelepon" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="supEmail" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="supAlamat" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('formSupplier').action = '<?= \App\Config\App::BASE_URL ?>/supplier/store';
    document.getElementById('modalTitle').textContent = 'Tambah Supplier';
    ['supNama','supTelepon','supEmail','supAlamat'].forEach(id => document.getElementById(id).value = '');
}
function editSupplier(id, nama, telepon, email, alamat) {
    document.getElementById('formSupplier').action = '<?= \App\Config\App::BASE_URL ?>/supplier/update/' + id;
    document.getElementById('modalTitle').textContent = 'Edit Supplier';
    document.getElementById('supNama').value = nama;
    document.getElementById('supTelepon').value = telepon || '';
    document.getElementById('supEmail').value = email || '';
    document.getElementById('supAlamat').value = alamat || '';
    new bootstrap.Modal(document.getElementById('modalSupplier')).show();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>