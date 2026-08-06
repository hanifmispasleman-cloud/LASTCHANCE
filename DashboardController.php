<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Transaksi;
use App\Models\Produk;

class DashboardController extends Controller {
    public function index() {
        $transaksiModel = new Transaksi();
        $produkModel = new Produk();
        $this->view('dashboard/index', [
            'title' => 'Dashboard - KasirKu',
            'user' => ['nama' => Session::get('user_nama'), 'role' => Session::get('user_role')],
            'total_penjualan' => $transaksiModel->getTotalPenjualanHariIni(),
            'total_transaksi' => $transaksiModel->getTotalTransaksiHariIni(),
            'total_produk' => $produkModel->count(['aktif' => 1]),
            'produk_hampir_habis' => $produkModel->getStokHampirHabis(),
            'grafik_7hari' => $transaksiModel->getPenjualan7Hari(),
            'transaksi_terbaru' => $transaksiModel->getAllWithRelations(['limit' => 10])
        ]);
    }
}