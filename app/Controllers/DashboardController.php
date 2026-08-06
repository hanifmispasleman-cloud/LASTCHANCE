<?php
/**
 * DashboardController - Controller untuk dashboard
 */

class DashboardController {
    
    private $userModel;
    private $produkModel;
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
        $this->userModel = new User($database);
        $this->produkModel = new Produk($database);
    }
    
    /**
     * Show dashboard
     */
    public function index() {
        AuthMiddleware::checkAuth();
        AuthMiddleware::checkSessionTimeout();
        SecurityMiddleware::setSecurityHeaders();
        
        $user = $_SESSION['user'];
        
        // Get stats
        $stats = [
            'total_sales' => $this->getTotalSales(),
            'total_transactions' => $this->getTotalTransactions(),
            'total_products' => $this->getTotalProducts(),
            'low_stock' => count($this->produkModel->getLowStock())
        ];
        
        // Get chart data
        $chart_data = $this->getChartData();
        $chart_labels = $this->getChartLabels();
        
        // Get recent transactions
        $recent_transactions = $this->getRecentTransactions();
        
        $page_title = 'Dashboard - KasirKu';
        
        include APP_PATH . 'Views/Dashboard/index.php';
    }
    
    /**
     * Get total sales this month
     */
    private function getTotalSales() {
        $query = "SELECT SUM(total) as total FROM transactions 
                  WHERE MONTH(created_at) = MONTH(NOW()) 
                  AND YEAR(created_at) = YEAR(NOW())";
        $result = $this->db->query($query);
        $data = $result->fetch_assoc();
        return $data['total'] ?? 0;
    }
    
    /**
     * Get total transactions this month
     */
    private function getTotalTransactions() {
        $query = "SELECT COUNT(*) as total FROM transactions 
                  WHERE MONTH(created_at) = MONTH(NOW()) 
                  AND YEAR(created_at) = YEAR(NOW())";
        $result = $this->db->query($query);
        $data = $result->fetch_assoc();
        return $data['total'] ?? 0;
    }
    
    /**
     * Get total products
     */
    private function getTotalProducts() {
        return $this->produkModel->count(['status' => 'active']);
    }
    
    /**
     * Get chart data (sales last 7 days)
     */
    private function getChartData() {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $query = "SELECT SUM(total) as total FROM transactions WHERE DATE(created_at) = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $date);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $data[] = $result['total'] ?? 0;
        }
        return $data;
    }
    
    /**
     * Get chart labels (dates)
     */
    private function getChartLabels() {
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = date('d/m', strtotime("-$i days"));
        }
        return $labels;
    }
    
    /**
     * Get recent transactions
     */
    private function getRecentTransactions() {
        $query = "SELECT * FROM transactions ORDER BY created_at DESC LIMIT 5";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

?>
