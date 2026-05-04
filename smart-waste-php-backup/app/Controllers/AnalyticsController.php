<?php
namespace App\Controllers;

use Core\Controller;

class AnalyticsController extends Controller {

    public function index() {
        require_once __DIR__ . '/../../includes/auth.php';
        auth_require_role(['Admin']);
        
        $data = [
            'title' => 'Analytics',
            'active' => 'analytics',
            'brand' => APP_BRAND_FULL,
        ];

        $this->render('analytics/index', $data);
    }
}
