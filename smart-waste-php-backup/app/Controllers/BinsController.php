<?php
namespace App\Controllers;

use Core\Controller;

class BinsController extends Controller {

    public function index() {
        require_once __DIR__ . '/../../includes/auth.php';
        auth_require_role(['Admin', 'Operator']);
        
        require_once __DIR__ . '/../../includes/db.php';
        
        $bins = db_get_bins();
        $binSource = db_bins_source();
        $binCount = count($bins);
        $generatedAt = gmdate('c');
        $fallbackBins = db_sample_bins();
        $firebaseReady = firebase_is_ready();
        $offlineWindowSeconds = 300;
        $latestPingTs = null;
        
        foreach ($bins as $candidate) {
            $ts = $this->bin_last_ping_ts($candidate);
            if ($ts !== null && ($latestPingTs === null || $ts > $latestPingTs)) {
                $latestPingTs = $ts;
            }
        }
        
        $piConnected = $latestPingTs !== null && (time() - $latestPingTs) <= $offlineWindowSeconds;
        $connectionState = [
            'firebase_ready' => $firebaseReady,
            'pi_connected' => $piConnected,
            'pi_last_ping_at' => $latestPingTs ? gmdate('c', $latestPingTs) : null,
        ];
        
        $newBinTemplate = [
            'battery' => 0,
            'fillBio' => 0,
            'fillRecyclable' => 0,
            'fillResidual' => 0,
            'status' => 'Offline',
            'lastPing' => null,
            'locationLat' => 0,
            'locationLng' => 0,
        ];
        
        $now = time();
        $registerableBins = array_values(array_filter($bins, function ($bin) use ($now, $offlineWindowSeconds) {
            $lastPing = $this->bin_last_ping_ts($bin);
            if ($lastPing && ($now - $lastPing) <= max($offlineWindowSeconds, 600)) {
                return true;
            }
            return false;
        }));
        
        $registerableBins = array_values(array_map(static function ($bin) {
            $id = $bin['bin_id'] ?? $bin['id'] ?? '';
            return [
                'bin_id' => $id,
                'name' => $bin['name'] ?? $id,
                'address' => $bin['address'] ?? 'Himamaylan City',
                'status' => $bin['status'] ?? '',
                'last_ping' => $bin['last_ping'] ?? $bin['lastPing'] ?? $bin['last_ping_at'] ?? null,
            ];
        }, $registerableBins));
        
        $existingBinLookup = [];
        foreach ($bins as $bin) {
            $binId = strtolower((string) ($bin['bin_id'] ?? $bin['id'] ?? ''));
            if ($binId !== '') {
                $existingBinLookup[$binId] = true;
            }
        }
        
        $pendingBins = [];
        foreach ($fallbackBins as $candidate) {
            $candidateId = strtolower((string) ($candidate['bin_id'] ?? $candidate['id'] ?? ''));
            if (!$candidateId || isset($existingBinLookup[$candidateId])) {
                continue;
            }
            $normalized = db_normalize_bin($candidate, $candidate['bin_id'] ?? null);
            if ($normalized) {
                $pendingBins[] = $normalized;
            }
        }

        $clientState = [
            'bins' => $bins,
            'source' => $binSource,
            'generated_at' => $generatedAt,
            'fallback' => $bins ?: $fallbackBins,
            'connection' => $connectionState,
            'bin_template' => $newBinTemplate,
            'registerable_bins' => $registerableBins,
            'pending_bins' => $pendingBins,
        ];
        
        $sourceIsLive = $binSource === 'firebase';
        $registerableCount = count($registerableBins);
        $pendingCount = count($pendingBins);
        $lastPingDisplay = $latestPingTs ? date('M j, Y g:i A T', $latestPingTs) : 'No telemetry yet';

        $data = [
            'title' => 'Bins',
            'active' => 'bins',
            'brand' => APP_BRAND_FULL,
            'bins' => $bins,
            'binCount' => $binCount,
            'piConnected' => $piConnected,
            'sourceIsLive' => $sourceIsLive,
            'lastPingDisplay' => $lastPingDisplay,
            'registerableCount' => $registerableCount,
            'pendingCount' => $pendingCount,
            'latestPingTs' => $latestPingTs,
            'clientState' => $clientState,
            'offlineWindowSeconds' => $offlineWindowSeconds,
            'registerableBins' => $registerableBins,
            'pendingBins' => $pendingBins
        ];

        $this->render('bins/index', $data);
    }
    
    // Helpers
    private function bin_last_ping_ts(array $bin): ?int {
        $value = $bin['last_ping'] ?? $bin['lastPing'] ?? $bin['last_ping_at'] ?? $bin['lastPingAt'] ?? $bin['last_updated'] ?? $bin['lastUpdated'] ?? null;
        if (!$value) {
            return null;
        }
        $ts = strtotime($value);
        return $ts ?: null;
    }
}
