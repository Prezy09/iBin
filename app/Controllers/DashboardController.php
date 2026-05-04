<?php
namespace App\Controllers;

use Core\Controller;

class DashboardController extends Controller {
    public function index() {
        require_once __DIR__ . '/../../includes/auth.php';
        auth_require_login();

        require_once __DIR__ . '/../../includes/db.php';
        
        $user_name = $_SESSION['user_name'] ?? 'User';
        
        $timezone = new \DateTimeZone('Asia/Manila');
        $nowDate = new \DateTime('now', $timezone);
        $now = $nowDate->format(\DateTime::ATOM);
        $liveBins = db_get_bins();
        $binSource = db_bins_source();
        $generatedAt = gmdate('c');
        $offlineWindowSeconds = 300;
        $sampleBins = $liveBins; // no mock data fallback; stay empty until real telemetry arrives
        $sampleBins = array_values($sampleBins);
        $sampleBins = array_map(static function ($bin) use ($now) {
            // Ensure dashboard bins always have expected keys; leave telemetry intact.
            if (!isset($bin['updated_at'])) {
                $bin['updated_at'] = $bin['last_updated'] ?? $now;
            }
            if (!isset($bin['compartments']) && isset($bin['fill_bio'])) {
                $bin['compartments'] = [
                    'biodegradable' => (int) ($bin['fill_bio'] ?? 0),
                    'recyclable' => (int) ($bin['fill_rec'] ?? 0),
                    'residual' => (int) ($bin['fill_res'] ?? 0),
                ];
            }
            if (!isset($bin['battery']) && isset($bin['battery_level'])) {
                $bin['battery'] = (int) $bin['battery_level'];
            }
            return $bin;
        }, $sampleBins);
        $sampleFillHistory = [
            'labels' => [],
            'average' => [],
            'threshold' => 80,
            'series' => []
        ];
        $highlightBin = $sampleBins[1] ?? $sampleBins[0] ?? [
            'bin_id' => 'no_bins',
            'name' => 'No bins yet',
            'address' => '',
            'compartments' => [
                'biodegradable' => 0,
                'recyclable' => 0,
                'residual' => 0,
            ],
            'battery' => 0,
        ];
        $sampleBinSnapshot = [
            'bin_id' => $highlightBin['bin_id'],
            'bin_name' => $highlightBin['name'],
            'labels' => ['Biodegradable', 'Recyclable', 'Residual'],
            'values' => [
                (int) ($highlightBin['compartments']['biodegradable'] ?? 0),
                (int) ($highlightBin['compartments']['recyclable'] ?? 0),
                (int) ($highlightBin['compartments']['residual'] ?? 0)
            ],
            'colors' => [
                'rgba(25, 135, 84, 0.85)',
                'rgba(13, 110, 253, 0.85)',
                'rgba(255, 193, 7, 0.85)'
            ],
            'note' => $sampleBins ? ('Snapshot derived from the latest telemetry for ' . $highlightBin['name']) : 'No bins ingested yet. Post telemetry to populate.'
        ];
        $sampleNetworkRegion = $sampleBins ? 'Himamaylan City, Negros Occidental' : 'No bins ingested yet';
        $recentActivity = [];
        $dashboardSampleSeed = [
            'network' => [
                'region' => $sampleNetworkRegion,
                'bins' => $sampleBins
            ],
            'fill_history' => $sampleFillHistory,
            'bin_snapshot' => $sampleBinSnapshot,
            'recent_activity' => $recentActivity
        ];
        
        $binAverages = array_map(function ($bin) {
            return $this->dashboard_percent_avg($bin['compartments'] ?? []);
        }, $sampleBins);
        $activeBinList = array_values(array_filter($sampleBins, function ($bin) use ($offlineWindowSeconds) {
            return $this->dashboard_is_online($bin, $offlineWindowSeconds);
        }));
        $activeBins = count($activeBinList);
        $binsNearFull = count(array_filter($sampleBins, function ($bin) {
            return $this->dashboard_bin_near_full($bin['compartments'] ?? []);
        }));
        $avgFillLevel = $binAverages ? (int) round(array_sum($binAverages) / count($binAverages)) : 0;
        $collectionsToday = min($activeBins, max(1, $binsNearFull + 1));
        $sampleBinLookup = [];
        foreach ($sampleBins as $binItem) {
            if (!empty($binItem['bin_id'])) {
                $sampleBinLookup[$binItem['bin_id']] = $binItem;
            }
        }

        $data = [
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'user_name' => $user_name,
            'activeBins' => $activeBins,
            'sampleNetworkRegion' => $sampleNetworkRegion,
            'binsNearFull' => $binsNearFull,
            'collectionsToday' => $collectionsToday,
            'avgFillLevel' => $avgFillLevel,
            'recentActivity' => $recentActivity,
            'sampleBinLookup' => $sampleBinLookup,
            'dashboardSampleSeed' => $dashboardSampleSeed,
            'offlineWindowSeconds' => $offlineWindowSeconds,
            'extra_head' => '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">'
        ];

        $this->render('dashboard/index', $data);
    }
    
    // Helpers
    private function dashboard_percent_avg(array $compartments): int {
        if (!$compartments) {
            return 0;
        }
        $values = array_map('intval', array_values($compartments));
        $count = count($values);
        return $count ? (int) round(array_sum($values) / $count) : 0;
    }
    
    private function dashboard_bin_near_full(array $compartments): bool {
        foreach ($compartments as $value) {
            if ((int) $value >= 80) {
                return true;
            }
        }
        return false;
    }
    
    private function dashboard_last_ping_ts(array $bin): ?int {
        $value = $bin['last_ping'] ?? $bin['lastPing'] ?? $bin['last_ping_at'] ?? $bin['lastPingAt'] ?? $bin['last_updated'] ?? $bin['updated_at'] ?? null;
        if (!$value) {
            return null;
        }
        $ts = strtotime($value);
        return $ts ?: null;
    }
    
    private function dashboard_is_online(array $bin, int $windowSeconds): bool {
        $ts = $this->dashboard_last_ping_ts($bin);
        if ($ts === null) {
            return false;
        }
        return (time() - $ts) <= max(60, $windowSeconds);
    }
}
