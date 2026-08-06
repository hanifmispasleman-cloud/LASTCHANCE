<?php
$pengaturan = (new \App\Models\Pengaturan())->getAllAsKeyValue();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= $pengaturan['nama_toko'] ?? 'KasirKu' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border-radius: 15px;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow login-card">
                    <div class="login-header">
                        <i class="bi bi-shop display-4"></i>
                        <h3 class="mt-2"><?= $pengaturan['nama_toko'] ?? 'KasirKu' ?></h3>
                        <p class="mb-0">Point of Sale</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (\App\Core\Session::hasFlash('error')): ?>
                        <div class="alert alert-danger"><?= \App\Core\Session::getFlash('error') ?></div>
                        <?php endif; ?>
                        <?php if (\App\Core\Session::hasFlash('success')): ?>
                        <div class="alert alert-success"><?= \App\Core\Session::getFlash('success') ?></div>
                        <?php endif; ?>
                        <form action="<?= \App\Config\App::BASE_URL ?>/login" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>
                        <p class="text-center text-muted mt-3 mb-0 small">
                            Default: admin / admin123
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>