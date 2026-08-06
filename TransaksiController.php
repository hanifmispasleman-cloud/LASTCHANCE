<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Transaksi;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Helpers\Helper;

class TransaksiController extends Controller {

    public function kasir() {
        $pelanggan = (new Pelanggan())->all('nama ASC');
        $this->view('transaksi/kasir', [
            'title' => 'Kasir - KasirKu',
            'pelanggan' => $pelanggan,
            'csrf_token' => Session::csrfToken(),
            'pajak_default' => Helper::getPengaturan('pajak') ?? 11
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        if (!Session::validateCsrf($input['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token keamanan tidak valid'], 403);
        }

        if (empty($input['items'])) {
            $this->json(['error' => 'Keranjang belanja kosong'], 400);
        }

        $noInvoice = Helper::generateInvoice();
        $total = (float)($input['total'] ?? 0);
        $diskon = (float)($input['diskon'] ?? 0);
        $pajak = (float)($input['pajak'] ?? 0);
        $grandTotal = $total - $diskon + $pajak;
        $bayar = (float)($input['bayar'] ?? 0);
        $metode = $input['metode_pembayaran'] ?? 'tunai';

        if ($metode === 'tunai' && $bayar < $grandTotal) {
            $this->json(['error' => 'Pembayaran kurang'], 400);
        }

        if ($metode !== 'tunai') {
            $bayar = $grandTotal;
            $kembalian = 0;
        } else {
            $kembalian = $bayar - $grandTotal;
        }

        $transaksiModel = new Transaksi();

        try {
            $transData = [
                'no_invoice' => $noInvoice,
                'id_user' => Session::get('user_id'),
                'id_pelanggan' => !empty($input['id_pelanggan']) ? $input['id_pelanggan'] : null,
                'total' => $total,
                'diskon' => $diskon,
                'pajak' => $pajak,
                'grand_total' => $grandTotal,
                'bayar' => $bayar,
                'kembalian' => $kembalian,
                'metode_pembayaran' => $metode,
                'catatan' => $input['catatan'] ?? null,
                'status' => 'sukses'
            ];

            $items = [];
            foreach ($input['items'] as $item) {
                $items[] = [
                    'id_produk' => $item['id'],
                    'qty' => $item['qty'],
                    'harga_satuan' => $item['harga_jual'],
                    'subtotal' => $item['subtotal']
                ];
            }

            $transId = $transaksiModel->createTransaksi($transData, $items);
            $transaksi = $transaksiModel->getWithDetail($transId);

            // Kirim token baru untuk transaksi berikutnya
            $this->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'transaksi' => $transaksi,
                'no_invoice' => $noInvoice,
                'new_csrf_token' => Session::csrfToken()
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()], 500);
        }
    }

    public function history() {
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-d'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
            'metode' => $_GET['metode'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        $transaksi = (new Transaksi())->getAllWithRelations($filters);

        $this->view('transaksi/history', [
            'title' => 'Riwayat Transaksi - KasirKu',
            'transaksi' => $transaksi,
            'filters' => $filters,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    public function detail($id) {
        $transaksi = (new Transaksi())->getWithDetail($id);

        if (!$transaksi) {
            Session::setFlash('error', 'Transaksi tidak ditemukan');
            $this->redirect(\App\Config\App::BASE_URL . '/transaksi/history');
        }

        if ($this->isAjax()) {
            $this->json($transaksi);
        }

        // Tampilkan struk atau detail
        $pengaturan = (new \App\Models\Pengaturan())->getAllAsKeyValue();
        $this->view('transaksi/struk', [
            'transaksi' => $transaksi,
            'pengaturan' => $pengaturan
        ]);
    }

    public function cetakStruk($id) {
        $transaksi = (new Transaksi())->getWithDetail($id);
        if (!$transaksi) {
            die('Transaksi tidak ditemukan');
        }

        $pengaturan = (new \App\Models\Pengaturan())->getAllAsKeyValue();

        // Output HTML struk untuk cetak thermal 58mm
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Struk <?= $transaksi['no_invoice'] ?></title>
            <style>
                @page { margin: 0; size: 58mm auto; }
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 11px;
                    width: 58mm;
                    margin: 0 auto;
                    padding: 5px;
                    line-height: 1.3;
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .garis { border-top: 1px dashed #000; margin: 5px 0; }
                .bold { font-weight: bold; }
                table { width: 100%; border-collapse: collapse; }
                td { padding: 2px 0; vertical-align: top; }
                .total-row td { font-weight: bold; font-size: 12px; }
                @media print {
                    body { -webkit-print-color-adjust: exact; }
                }
            </style>
        </head>
        <body>
            <div class="text-center">
                <div style="font-size:14px;font-weight:bold;"><?= strtoupper($pengaturan['nama_toko'] ?? 'KasirKu') ?></div>
                <div style="font-size:10px;"><?= $pengaturan['alamat'] ?? '' ?></div>
                <div style="font-size:10px;">Telp: <?= $pengaturan['telepon'] ?? '' ?></div>
            </div>
            <div class="garis"></div>
            <div>
                <table>
                    <tr><td>No</td><td>:</td><td><?= $transaksi['no_invoice'] ?></td></tr>
                    <tr><td>Tgl</td><td>:</td><td><?= Helper::tanggal($transaksi['created_at'], 'd/m/Y H:i') ?></td></tr>
                    <tr><td>Kasir</td><td>:</td><td><?= $transaksi['nama_kasir'] ?></td></tr>
                    <?php if ($transaksi['nama_pelanggan']): ?>
                    <tr><td>Plg</td><td>:</td><td><?= $transaksi['nama_pelanggan'] ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="garis"></div>
            <table>
                <?php foreach ($transaksi['detail'] as $item): ?>
                <tr>
                    <td colspan="3"><?= $item['nama_produk'] ?></td>
                </tr>
                <tr>
                    <td style="width:20%;"><?= $item['qty'] ?>x</td>
                    <td style="width:40%;"><?= Helper::rupiah($item['harga_satuan']) ?></td>
                    <td style="width:40%;" class="text-right"><?= Helper::rupiah($item['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <div class="garis"></div>
            <table>
                <tr><td>Total</td><td class="text-right"><?= Helper::rupiah($transaksi['total']) ?></td></tr>
                <?php if ($transaksi['diskon'] > 0): ?>
                <tr><td>Diskon</td><td class="text-right"><?= Helper::rupiah($transaksi['diskon']) ?></td></tr>
                <?php endif; ?>
                <?php if ($transaksi['pajak'] > 0): ?>
                <tr><td>Pajak</td><td class="text-right"><?= Helper::rupiah($transaksi['pajak']) ?></td></tr>
                <?php endif; ?>
                <tr class="total-row"><td>TOTAL</td><td class="text-right"><?= Helper::rupiah($transaksi['grand_total']) ?></td></tr>
                <tr><td>Bayar</td><td class="text-right"><?= Helper::rupiah($transaksi['bayar']) ?></td></tr>
                <tr><td>Kembali</td><td class="text-right"><?= Helper::rupiah($transaksi['kembalian']) ?></td></tr>
                <tr><td>Metode</td><td class="text-right"><?= strtoupper($transaksi['metode_pembayaran']) ?></td></tr>
            </table>
            <div class="garis"></div>
            <div class="text-center" style="font-size:10px;">
                <?= $pengaturan['footer_struk'] ?? 'Terima kasih' ?>
            </div>
            <div class="text-center" style="margin-top:5px;">===</div>
            <script>window.onload = function() { window.print(); }</script>
        </body>
        </html>
        <?php
        exit;
    }
}