<?php
namespace App\Models;

use App\Core\Model;
use App\Helpers\Helper;

class Transaksi extends Model {
    protected $table = 'transaksi';

    public function getAllWithRelations($filters = []) {
        $sql = "SELECT t.*, u.nama as nama_kasir, p.nama as nama_pelanggan FROM transaksi t
                LEFT JOIN users u ON t.id_user = u.id LEFT JOIN pelanggan p ON t.id_pelanggan = p.id WHERE 1=1";
        $params = [];
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND DATE(t.created_at) BETWEEN :start_date AND :end_date";
            $params['start_date'] = $filters['start_date']; $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['metode'])) { $sql .= " AND t.metode_pembayaran = :metode"; $params['metode'] = $filters['metode']; }
        if (!empty($filters['status'])) { $sql .= " AND t.status = :status"; $params['status'] = $filters['status']; }
        $sql .= " ORDER BY t.created_at DESC";
        if (!empty($filters['limit'])) $sql .= " LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) $stmt->bindValue($key, $val);
        if (!empty($filters['limit'])) $stmt->bindValue('limit', (int)$filters['limit'], \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getWithDetail($id) {
        $sql = "SELECT t.*, u.nama as nama_kasir, p.nama as nama_pelanggan FROM transaksi t
                LEFT JOIN users u ON t.id_user = u.id LEFT JOIN pelanggan p ON t.id_pelanggan = p.id WHERE t.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $transaksi = $stmt->fetch();
        if ($transaksi) {
            $sqlDetail = "SELECT dt.*, pr.nama as nama_produk, pr.kode as kode_produk FROM detail_transaksi dt
                          JOIN produk pr ON dt.id_produk = pr.id WHERE dt.id_transaksi = :id_transaksi";
            $stmtDetail = $this->db->prepare($sqlDetail);
            $stmtDetail->execute(['id_transaksi' => $id]);
            $transaksi['detail'] = $stmtDetail->fetchAll();
        }
        return $transaksi;
    }

    public function getTotalPenjualanHariIni() {
        return (float) $this->db->query("SELECT COALESCE(SUM(grand_total),0) as total FROM transaksi WHERE DATE(created_at) = CURDATE() AND status = 'sukses'")->fetch()['total'];
    }

    public function getTotalTransaksiHariIni() {
        return (int) $this->db->query("SELECT COUNT(*) as total FROM transaksi WHERE DATE(created_at) = CURDATE() AND status = 'sukses'")->fetch()['total'];
    }

    public function getPenjualan7Hari() {
        $sql = "SELECT DATE(created_at) as tanggal, COALESCE(SUM(grand_total),0) as total FROM transaksi
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = 'sukses' GROUP BY DATE(created_at) ORDER BY tanggal ASC";
        $results = $this->db->query($sql)->fetchAll();
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayName = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            $data[$date] = ['label' => $dayName[date('w', strtotime($date))], 'tanggal' => $date, 'total' => 0];
        }
        foreach ($results as $row) { if (isset($data[$row['tanggal']])) $data[$row['tanggal']]['total'] = (float)$row['total']; }
        return array_values($data);
    }

    public function getLaporanPeriode($startDate, $endDate, $groupBy = 'DATE') {
        $format = $groupBy === 'MONTH' ? '%Y-%m' : ($groupBy === 'YEAR' ? '%Y' : '%Y-%m-%d');
        $sql = "SELECT DATE_FORMAT(created_at, '{$format}') as periode, COUNT(*) as total_transaksi,
                COALESCE(SUM(grand_total),0) as total_penjualan, COALESCE(SUM(diskon),0) as total_diskon,
                COALESCE(SUM(pajak),0) as total_pajak FROM transaksi
                WHERE DATE(created_at) BETWEEN :start_date AND :end_date AND status = 'sukses'
                GROUP BY periode ORDER BY periode ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll();
    }

    public function createTransaksi($data, $items) {
        $this->beginTransaction();
        try {
            $transId = $this->create($data);
            $detailModel = new DetailTransaksi();
            foreach ($items as $item) {
                $detailModel->create([
                    'id_transaksi' => $transId,
                    'id_produk' => $item['id_produk'],
                    'qty' => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['subtotal']
                ]);
                (new Produk())->updateStok($item['id_produk'], -$item['qty']);
            }
            $this->commit();
            return $transId;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}

class DetailTransaksi extends Model {
    protected $table = 'detail_transaksi';
}