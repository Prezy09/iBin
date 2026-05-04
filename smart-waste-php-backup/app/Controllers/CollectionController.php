<?php
namespace App\Controllers;

use Core\Controller;

class CollectionController extends Controller {

    public function index() {
        require_once __DIR__ . '/../../includes/auth.php';
        auth_require_role(['Admin', 'Operator']);
        
        require_once __DIR__ . '/../../includes/db.php';
        
        $userRole = $_SESSION['user_role'] ?? 'User';
        $isOperator = $userRole === 'Operator';
        
        $liveBins = db_get_bins();
        $binSource = db_bins_source();
        $generatedAt = gmdate('c');
        $sampleBins = $liveBins ?: db_sample_bins();
        
        $clientState = [
            'bins' => $liveBins,
            'source' => $binSource,
            'generated_at' => $generatedAt,
            'sample' => $sampleBins,
        ];
        
        $clientStateJson = json_encode(
            $clientState,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES
        );
        
        $liveBinCount = is_array($liveBins) ? count($liveBins) : 0;
        $sampleBinCount = is_array($sampleBins) ? count($sampleBins) : 0;
        $binSourceLabel = strtolower((string) $binSource);
        $isLiveSource = $binSourceLabel === 'firebase';

        $data = [
            'title' => 'Collection Routes',
            'active' => 'collection',
            'brand' => APP_BRAND_FULL,
            'userRole' => $userRole,
            'isOperator' => $isOperator,
            'liveBins' => $liveBins,
            'binSource' => $binSource,
            'sampleBins' => $sampleBins,
            'clientStateJson' => $clientStateJson,
            'liveBinCount' => $liveBinCount,
            'sampleBinCount' => $sampleBinCount,
            'isLiveSource' => $isLiveSource,
        ];

        $this->render('collection/index', $data);
    }
}
