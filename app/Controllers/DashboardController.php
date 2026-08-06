<?php
/**
 * Dashboard Controller - Controller untuk Dashboard
 */

require_once APP_DIR . 'Models/Transaksi.php';
require_once APP_DIR . 'Models/Produk.php';
require_once APP_DIR . 'Models/DetailTransaksi.php';
require_once APP_DIR . 'Models/Kategori.php';

class DashboardController {
    
    private $db;
    private $transaksiModel;
    private $produkModel;
    private $detailTransaksiModel;
    private $kategoriModel;
    
    public function __construct($database) {
        $this->db = $database;
        $this->transaksiModel = new Transaksi($database);
        $this->produkModel = new Produk($database);
        $this->detailTransaksiModel = new DetailTransaksi($database);
        $this->kategoriModel = new Kategori($database);
    }
    
    /**
     * Show dashboard
     */
    public function index() {
        AuthMiddleware::requireLogin();
        SecurityMiddleware::preventCache();
        
        // Get dashboard data
        $total_sales_today = $this->transaksiModel->getTotalTodaysSales();
        $total_transactions_today = $this->transaksiModel->getTotalTodaysTransactions();
        $total_products = $this->produkModel->count();
        $low_stock_products = $this->produkModel->getLowStock(10);
        $sales_last_7_days = $this->transaksiModel->getSalesLast7Days();
        $latest_transactions = $this->transaksiModel->getLatestTransactions(10);
        $top_products = $this->detailTransaksiModel->getTopProducts(5);
        
        // Prepare chart data
        $chart_labels = [];
        $chart_data = [];
        
        foreach ($sales_last_7_days as $sale) {
            $chart_labels[] = formatDate($sale['tanggal']);
            $chart_data[] = $sale['total_penjualan'];
        }
        
        $title = 'Dashboard - KasirKu';
        
        require VIEW_DIR . 'dashboard/index.php';
    }
    
    /**
     * Get dashboard data as JSON (for AJAX)
     */
    public function getData() {
        AuthMiddleware::requireLogin();
        header('Content-Type: application/json');
        
        $total_sales_today = $this->transaksiModel->getTotalTodaysSales();
        $total_transactions_today = $this->transaksiModel->getTotalTodaysTransactions();
        $total_products = $this->produkModel->count();
        $low_stock_products = $this->produkModel->getLowStock(10);
        $latest_transactions = $this->transaksiModel->getLatestTransactions(10);
        
        $response = [
            'success' => true,
            'data' => [
                'total_sales_today' => $total_sales_today,
                'total_transactions_today' => $total_transactions_today,
                'total_products' => $total_products,
                'low_stock_count' => count($low_stock_products),
                'low_stock_products' => $low_stock_products,
                'latest_transactions' => $latest_transactions
            ]
        ];
        
        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit();
    }
}

?>
