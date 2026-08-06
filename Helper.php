<?php
namespace App\Helpers;

class Helper {
    public static function rupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    public static function tanggal($tanggal, $format = 'd/m/Y H:i') {
        return date($format, strtotime($tanggal));
    }

    public static function tanggalIndonesia($tanggal) {
        $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $t = strtotime($tanggal);
        return date('d', $t) . ' ' . $bulan[(int)date('m', $t)] . ' ' . date('Y', $t);
    }

    public static function generateInvoice() {
        return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }

    public static function generateKodeProduk() {
        return 'BRG-' . strtoupper(substr(uniqid(), -6));
    }

    public static function getPengaturan($kunci) {
        $db = \App\Config\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT nilai FROM pengaturan WHERE kunci = :kunci");
        $stmt->execute(['kunci' => $kunci]);
        $result = $stmt->fetch();
        return $result ? $result['nilai'] : '';
    }
}