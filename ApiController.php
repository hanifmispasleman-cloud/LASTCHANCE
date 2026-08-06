<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Kategori;

class ApiController extends Controller {

    public function searchProduk() {
        $keyword = $_GET['keyword'] ?? '';
        $produkModel = new Produk();
        $produk = $produkModel->searchLive($keyword);
        $this->json($produk);
    }

    public function getProdukByBarcode($barcode) {
        $produkModel = new Produk();
        $produk = $produkModel->findOneBy('barcode', $barcode);
        if (!$produk) {
            $produk = $produkModel->findOneBy('kode', $barcode);
        }
        if ($produk) {
            $this->json($produk);
        } else {
            $this->json(['error' => 'Produk tidak ditemukan'], 404);
        }
    }

    public function searchPelanggan() {
        $keyword = $_GET['keyword'] ?? '';
        $pelangganModel = new Pelanggan();
        $pelanggan = $pelangganModel->search($keyword);
        $this->json($pelanggan);
    }

    public function getKategori() {
        $kategoriModel = new Kategori();
        $kategori = $kategoriModel->all('nama ASC');
        $this->json($kategori);
    }
}