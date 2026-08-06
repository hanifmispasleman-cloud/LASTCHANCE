<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Transaksi;
use App\Models\Produk;
use App\Helpers\Helper;

class LaporanController extends Controller {

    public function index() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate   = $_GET['end_date'] ?? date('Y-m-d');
        $periode   = $_GET['periode'] ?? 'harian';

        $transaksiModel = new Transaksi();
        $produkModel    = new Produk();

        $groupBy = $periode === 'bulanan' ? 'MONTH' : ($periode === 'tahunan' ? 'YEAR' : 'DATE');
        $laporan = $transaksiModel->getLaporanPeriode($startDate, $endDate, $groupBy);
        $produkTerlaris = $produkModel->getProdukTerlaris(10, $startDate, $endDate);

        // Total
        $totalPenjualan  = array_sum(array_column($laporan, 'total_penjualan'));
        $totalTransaksi  = array_sum(array_column($laporan, 'total_transaksi'));
        $totalDiskon     = array_sum(array_column($laporan, 'total_diskon'));
        $totalPajak      = array_sum(array_column($laporan, 'total_pajak'));

        $this->view('laporan/index', [
            'title'           => 'Laporan - KasirKu',
            'laporan'         => $laporan,
            'produk_terlaris' => $produkTerlaris,
            'start_date'      => $startDate,
            'end_date'        => $endDate,
            'periode'         => $periode,
            'total_penjualan' => $totalPenjualan,
            'total_transaksi' => $totalTransaksi,
            'total_diskon'    => $totalDiskon,
            'total_pajak'     => $totalPajak
        ]);
    }

    public function exportPdf() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate   = $_GET['end_date'] ?? date('Y-m-d');

        $transaksiModel = new Transaksi();
        $laporan = $transaksiModel->getLaporanPeriode($startDate, $endDate, 'DATE');
        $pengaturan = (new \App\Models\Pengaturan())->getAllAsKeyValue();

        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Laporan Penjualan</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #000; padding: 5px; text-align: left; }
                th { background: #eee; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .header { text-align: center; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2><?= $pengaturan['nama_toko'] ?? 'KasirKu' ?></h2>
                <p><?= $pengaturan['alamat'] ?? '' ?></p>
                <h3>Laporan Penjualan</h3>
                <p>Periode: <?= Helper::tanggalIndonesia($startDate) ?> - <?= Helper::tanggalIndonesia($endDate) ?></p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Total Transaksi</th>
                        <th>Total Penjualan</th>
                        <th>Diskon</th>
                        <th>Pajak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($laporan as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= Helper::tanggalIndonesia($row['periode']) ?></td>
                        <td class="text-center"><?= $row['total_transaksi'] ?></td>
                        <td class="text-right"><?= Helper::rupiah($row['total_penjualan']) ?></td>
                        <td class="text-right"><?= Helper::rupiah($row['total_diskon']) ?></td>
                        <td class="text-right"><?= Helper::rupiah($row['total_pajak']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight:bold;">
                        <td colspan="2">TOTAL</td>
                        <td class="text-center"><?= array_sum(array_column($laporan, 'total_transaksi')) ?></td>
                        <td class="text-right"><?= Helper::rupiah(array_sum(array_column($laporan, 'total_penjualan'))) ?></td>
                        <td class="text-right"><?= Helper::rupiah(array_sum(array_column($laporan, 'total_diskon'))) ?></td>
                        <td class="text-right"><?= Helper::rupiah(array_sum(array_column($laporan, 'total_pajak'))) ?></td>
                    </tr>
                </tfoot>
            </table>
            <script>window.print();</script>
        </body>
        </html>
        <?php
        exit;
    }

    public function exportExcel() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate   = $_GET['end_date'] ?? date('Y-m-d');

        $transaksiModel = new Transaksi();
        $laporan = $transaksiModel->getLaporanPeriode($startDate, $endDate, 'DATE');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="laporan_penjualan_' . $startDate . '_' . $endDate . '.xls"');

        echo "No\tTanggal\tTotal Transaksi\tTotal Penjualan\tDiskon\tPajak\n";
        $no = 1;
        foreach ($laporan as $row) {
            echo "{$no}\t{$row['periode']}\t{$row['total_transaksi']}\t{$row['total_penjualan']}\t{$row['total_diskon']}\t{$row['total_pajak']}\n";
            $no++;
        }
        exit;
    }
}