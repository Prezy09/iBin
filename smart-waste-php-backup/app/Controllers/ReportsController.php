<?php
namespace App\Controllers;

use Core\Controller;

class ReportsController extends Controller {

    public function index() {
        require_once __DIR__ . '/../../includes/auth.php';
        auth_require_role(['Admin']);
        
        $data = [
            'title' => 'Reports',
            'active' => 'reports',
            'brand' => APP_BRAND_FULL,
        ];

        $this->render('reports/index', $data);
    }
}
