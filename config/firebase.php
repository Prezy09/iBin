<?php
// config/firebase.php
// Firebase helpers using Realtime Database REST API + service account access tokens.

declare(strict_types=1);

if (!function_exists('firebase_env')) {
  /**
   * Load .env.php values once for Firebase usage.
   */
  function firebase_env(): array {
    static $env = null;
    if ($env !== null) {
      return $env;
    }
    $envPath = __DIR__ . '/../.env.php';
    $env = file_exists($envPath) ? include $envPath : [];
    if (!is_array($env)) {
      $env = [];
    }
    return $env;
  }
}

if (!function_exists('firebase_database_url')) {
  function firebase_database_url(): string {
    $env = firebase_env();
    $url = trim((string) ($env['FIREBASE_DATABASE_URL'] ?? ''));
    if ($url === '') {
      throw new RuntimeException('FIREBASE_DATABASE_URL is not configured.');
    }
    return rtrim($url, '/');
  }
}

if (!function_exists('firebase_is_ready')) {
  function firebase_is_ready(): bool {
    static $didLog = false;
    try {
      firebase_database_url();
      firebase_service_account();
      return true;
    } catch (Throwable $e) {
      if (!$didLog) {
        error_log('[firebase] configuration incomplete: ' . $e->getMessage());
        $didLog = true;
      }
      return false;
    }
  }
}

if (!function_exists('firebase_service_account')) {
  function firebase_service_account(): array {
    static $service = null;
    if ($service !== null) {
      return $service;
    }
    $env = firebase_env();
    $fromFile = trim((string) ($env['FIREBASE_CREDENTIALS'] ?? ''));
    if ($fromFile !== '') {
      $path = $fromFile;
      $hasDrive = preg_match('#^[A-Za-z]:\\\\#', $path) === 1;
      $isUnixAbsolute = isset($path[0]) && $path[0] === '/';
      if (!$hasDrive && !$isUnixAbsolute) {
        $path = __DIR__ . '/../' . ltrim($path, '/');
      }
      if (!file_exists($path)) {
        throw new RuntimeException('Firebase credentials file not found: ' . $path);
      }
      $decoded = json_decode((string) file_get_contents($path), true);
      if (!is_array($decoded)) {
        throw new RuntimeException('Unable to parse Firebase credentials JSON.');
      }
      $service = [
        'client_email' => $decoded['client_email'] ?? '',
        'private_key' => $decoded['private_key'] ?? '',
      ];
    } else {
      $clientEmail = trim((string) ($env['FIREBASE_CLIENT_EMAIL'] ?? ''));
      $privateKey = (string) ($env['FIREBASE_PRIVATE_KEY'] ?? '');
      $privateKey = str_replace(["\\n", "\r\n"], "\n", $privateKey);
      $service = [
        'client_email' => $clientEmail,
        'private_key' => $privateKey,
      ];
    }

    if (empty($service['client_email']) || empty($service['private_key'])) {
      throw new RuntimeException('Firebase service account is missing client_email/private_key.');
    }
    return $service;
  }
}

if (!function_exists('firebase_get_access_token')) {
  function firebase_get_access_token(): string {
    static $cache = null;
    $now = time();
    if ($cache && ($cache['expires_at'] ?? 0) > ($now + 60)) {
      return $cache['token'];
    }

    $service = firebase_service_account();
    $jwt = firebase_build_jwt($service);
    $response = firebase_http_request(
      'POST',
      'https://oauth2.googleapis.com/token',
      [
        'Content-Type: application/x-www-form-urlencoded',
      ],
      http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt,
      ], '', '&', PHP_QUERY_RFC3986)
    );
    $payload = json_decode($response, true);
    if (!is_array($payload) || empty($payload['access_token'])) {
      throw new RuntimeException('Unable to retrieve Firebase access token.');
    }
    $expiresIn = (int) ($payload['expires_in'] ?? 3600);
    $cache = [
      'token' => $payload['access_token'],
      'expires_at' => $now + max(60, $expiresIn - 30),
    ];
    return $cache['token'];
  }
}

if (!function_exists('firebase_build_jwt')) {
  function firebase_build_jwt(array $service): string {
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $now = time();
    $claims = [
      'iss' => $service['client_email'],
      'scope' => 'https://www.googleapis.com/auth/firebase.database https://www.googleapis.com/auth/userinfo.email',
      'aud' => 'https://oauth2.googleapis.com/token',
      'iat' => $now,
      'exp' => $now + 3600,
    ];
    $segments = [
      firebase_base64url_encode(json_encode($header, JSON_UNESCAPED_SLASHES)),
      firebase_base64url_encode(json_encode($claims, JSON_UNESCAPED_SLASHES)),
    ];
    $input = implode('.', $segments);
    $signature = '';
    if (!openssl_sign($input, $signature, $service['private_key'], 'SHA256')) {
      throw new RuntimeException('Unable to sign Firebase JWT (check private key).');
    }
    $segments[] = firebase_base64url_encode($signature);
    return implode('.', $segments);
  }
}

if (!function_exists('firebase_base64url_encode')) {
  function firebase_base64url_encode(string $value): string {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
  }
}

if (!function_exists('firebase_http_request')) {
  function firebase_http_request(string $method, string $url, array $headers = [], ?string $body = null): string {
    if (!function_exists('curl_init')) {
      throw new RuntimeException('The cURL extension is required for Firebase HTTP requests.');
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => strtoupper($method),
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_HTTPHEADER => array_merge(['User-Agent: smart-waste-php/1.0'], $headers),
    ]);
    if ($body !== null) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $response = curl_exec($ch);
    if ($response === false) {
      $error = curl_error($ch);
      curl_close($ch);
      throw new RuntimeException('Firebase HTTP request failed: ' . $error);
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($status < 200 || $status >= 300) {
      throw new RuntimeException(sprintf('Firebase HTTP request returned status %d: %s', $status, $response));
    }
    return $response;
  }
}

if (!function_exists('firebase_db_request')) {
  function firebase_db_request(string $method, string $path, array $query = [], $payload = null) {
    $base = firebase_database_url();
    $normalizedPath = trim($path, '/');
    $url = $base . '/' . $normalizedPath . '.json';
    $query['access_token'] = firebase_get_access_token();
    $qs = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    if ($qs) {
      $url .= '?' . $qs;
    }
    $body = null;
    if ($payload !== null) {
      $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
    }
    $response = firebase_http_request($method, $url, ['Content-Type: application/json'], $body);
    if ($response === '' || strtolower($response) === 'null') {
      return null;
    }
    $decoded = json_decode($response, true);
    if (isset($decoded['error'])) {
      throw new RuntimeException('Firebase DB error: ' . $decoded['error']);
    }
    return $decoded;
  }
}

if (!function_exists('firebase_db_get')) {
  function firebase_db_get(string $path, array $query = []) {
    return firebase_db_request('GET', $path, $query);
  }
}

if (!function_exists('firebase_db_put')) {
  function firebase_db_put(string $path, $payload) {
    return firebase_db_request('PUT', $path, [], $payload);
  }
}

if (!function_exists('firebase_db_patch')) {
  function firebase_db_patch(string $path, $payload) {
    return firebase_db_request('PATCH', $path, [], $payload);
  }
}

if (!function_exists('firebase_db_delete')) {
  function firebase_db_delete(string $path) {
    return firebase_db_request('DELETE', $path);
  }
}

if (!function_exists('firebase_users_path')) {
  function firebase_users_path(): string {
    return 'users';
  }
}

if (!function_exists('firebase_users_generate_id')) {
  function firebase_users_generate_id(?string $email = null): string {
    try {
      return 'usr_' . bin2hex(random_bytes(8));
    } catch (Throwable $e) {
      $base = substr(preg_replace('/[^a-z0-9]/i', '', $email ?? ''), 0, 6) ?: 'user';
      return strtolower($base) . '_' . uniqid();
    }
  }
}

if (!function_exists('firebase_users_find_by_email')) {
  function firebase_users_find_by_email(string $email): ?array {
    if ($email === '') {
      return null;
    }
    $targets = [
      ['field' => 'email_lower', 'value' => strtolower($email)],
    ];
    if (strtolower($email) !== $email) {
      $targets[] = ['field' => 'email', 'value' => $email];
    }
    foreach ($targets as $target) {
      $result = firebase_db_get(firebase_users_path(), [
        'orderBy' => json_encode($target['field']),
        'equalTo' => json_encode($target['value']),
        'limitToFirst' => 1,
      ]);
      $user = firebase_users_from_query($result);
      if ($user) {
        return $user;
      }
    }
    return null;
  }
}

if (!function_exists('firebase_users_get')) {
  function firebase_users_get(string $id): ?array {
    $user = firebase_db_get(firebase_users_path() . '/' . $id);
    return is_array($user) ? firebase_users_normalize($user, $id) : null;
  }
}

if (!function_exists('firebase_users_create')) {
  function firebase_users_create(array $data): string {
    $id = firebase_users_generate_id($data['email'] ?? null);
    $emailOriginal = trim((string) ($data['email'] ?? ''));
    $data['id'] = $id;
    $data['email'] = $emailOriginal;
    $data['email_lower'] = strtolower($emailOriginal);
    firebase_db_put(firebase_users_path() . '/' . $id, $data);
    return $id;
  }
}

if (!function_exists('firebase_users_update')) {
  function firebase_users_update(string $id, array $data): void {
    if (isset($data['email'])) {
      $data['email_lower'] = strtolower(trim((string) $data['email']));
    }
    firebase_db_patch(firebase_users_path() . '/' . $id, $data);
  }
}

if (!function_exists('firebase_users_delete')) {
  function firebase_users_delete(string $id): void {
    firebase_db_delete(firebase_users_path() . '/' . $id);
  }
}

if (!function_exists('firebase_users_from_query')) {
  function firebase_users_from_query($result): ?array {
    if (!is_array($result) || empty($result)) {
      return null;
    }
    $id = array_key_first($result);
    $user = $result[$id];
    return is_array($user) ? firebase_users_normalize($user, $id) : null;
  }
}

if (!function_exists('firebase_users_normalize')) {
  function firebase_users_normalize(array $user, ?string $id = null): array {
    if ($id && empty($user['id'])) {
      $user['id'] = $id;
    }
    if (!isset($user['email_lower']) && isset($user['email'])) {
      $user['email_lower'] = strtolower($user['email']);
    }
    return $user;
  }
}

if (!function_exists('firebase_access_requests_path')) {
  function firebase_access_requests_path(): string {
    return 'access_requests';
  }
}

if (!function_exists('firebase_access_request_generate_id')) {
  function firebase_access_request_generate_id(): string {
    try {
      return 'req_' . bin2hex(random_bytes(6));
    } catch (Throwable $e) {
      return 'req_' . uniqid();
    }
  }
}

if (!function_exists('firebase_access_request_normalize')) {
  function firebase_access_request_normalize(array $request, ?string $id = null): array {
    if ($id && empty($request['id'])) {
      $request['id'] = $id;
    }
    if (isset($request['email']) && !isset($request['email_lower'])) {
      $request['email_lower'] = strtolower((string) $request['email']);
    }
    if (empty($request['status'])) {
      $request['status'] = 'Pending';
    }
    return $request;
  }
}

if (!function_exists('firebase_access_request_create')) {
  function firebase_access_request_create(array $data): string {
    $id = firebase_access_request_generate_id();
    $emailOriginal = trim((string) ($data['email'] ?? ''));
    $payload = array_merge($data, [
      'id' => $id,
      'email' => $emailOriginal,
      'email_lower' => strtolower($emailOriginal),
      'status' => 'Pending',
      'submitted_at' => $data['submitted_at'] ?? gmdate('c'),
    ]);
    firebase_db_put(firebase_access_requests_path() . '/' . $id, $payload);
    return $id;
  }
}

if (!function_exists('firebase_access_request_get')) {
  function firebase_access_request_get(string $id): ?array {
    $request = firebase_db_get(firebase_access_requests_path() . '/' . $id);
    return is_array($request) ? firebase_access_request_normalize($request, $id) : null;
  }
}

if (!function_exists('firebase_access_requests_find_by_email')) {
  function firebase_access_requests_find_by_email(string $email, ?array $statuses = null): ?array {
    if ($email === '') {
      return null;
    }
    $targets = [
      ['field' => 'email_lower', 'value' => strtolower($email)],
    ];
    if (strtolower($email) !== $email) {
      $targets[] = ['field' => 'email', 'value' => $email];
    }

    $fallback = false;
    foreach ($targets as $target) {
      try {
        $result = firebase_db_get(firebase_access_requests_path(), [
          'orderBy' => json_encode($target['field']),
          'equalTo' => json_encode($target['value']),
          'limitToFirst' => 1,
        ]);
        if (!is_array($result) || empty($result)) {
          continue;
        }
        $id = array_key_first($result);
        $request = $result[$id];
        if (!is_array($request)) {
          continue;
        }
        $normalized = firebase_access_request_normalize($request, $id);
        if ($statuses !== null && !in_array($normalized['status'] ?? '', $statuses, true)) {
          continue;
        }
        return $normalized;
      } catch (Throwable $e) {
        $fallback = true;
      }
    }

    if ($fallback) {
      try {
        $rawAll = firebase_db_get(firebase_access_requests_path());
        if (is_array($rawAll)) {
          foreach ($rawAll as $id => $request) {
            if (!is_array($request)) {
              continue;
            }
            $normalized = firebase_access_request_normalize($request, (string) $id);
            $matchesEmail = strcasecmp($normalized['email_lower'] ?? '', strtolower($email)) === 0
              || strcasecmp($normalized['email'] ?? '', $email) === 0;
            if (!$matchesEmail) {
              continue;
            }
            if ($statuses !== null && !in_array($normalized['status'] ?? '', $statuses, true)) {
              continue;
            }
            return $normalized;
          }
        }
      } catch (Throwable $e2) {
        // Swallow; return null
      }
    }

    return null;
  }
}

if (!function_exists('firebase_access_requests_pending')) {
  function firebase_access_requests_pending(): array {
    $requests = [];
    $fallback = false;
    try {
      $raw = firebase_db_get(firebase_access_requests_path(), [
        'orderBy' => json_encode('status'),
        'equalTo' => json_encode('Pending'),
      ]);
      if (is_array($raw)) {
        foreach ($raw as $id => $request) {
          if (!is_array($request)) {
            continue;
          }
          $requests[] = firebase_access_request_normalize($request, (string) $id);
        }
      }
    } catch (Throwable $e) {
      // Likely missing indexOn in Firebase rules; fallback to scan all then filter in PHP.
      $fallback = true;
    }

    if ($fallback) {
      try {
        $rawAll = firebase_db_get(firebase_access_requests_path());
        if (is_array($rawAll)) {
          foreach ($rawAll as $id => $request) {
            if (!is_array($request)) {
              continue;
            }
            $normalized = firebase_access_request_normalize($request, (string) $id);
            if (strcasecmp($normalized['status'] ?? '', 'Pending') === 0) {
              $requests[] = $normalized;
            }
          }
        }
      } catch (Throwable $e2) {
        // swallow; let empty array bubble up
      }
    }

    usort($requests, static function ($a, $b) {
      return strcmp($b['submitted_at'] ?? '', $a['submitted_at'] ?? '');
    });
    return $requests;
  }
}

if (!function_exists('firebase_access_request_update')) {
  function firebase_access_request_update(string $id, array $data): void {
    if (isset($data['email'])) {
      $data['email_lower'] = strtolower(trim((string) $data['email']));
    }
    firebase_db_patch(firebase_access_requests_path() . '/' . $id, $data);
  }
}

if (!function_exists('firebase_access_request_update_status')) {
  function firebase_access_request_update_status(string $id, string $status, array $extra = []): void {
    $payload = array_merge($extra, [
      'status' => $status,
      'decided_at' => $extra['decided_at'] ?? gmdate('c'),
    ]);
    firebase_access_request_update($id, $payload);
  }
}

if (!function_exists('array_is_list')) {
  function array_is_list(array $array): bool {
    if (function_exists('\array_is_list')) {
      return \array_is_list($array);
    }
    $expectedKey = 0;
    foreach ($array as $key => $_) {
      if ($key !== $expectedKey) {
        return false;
      }
      $expectedKey++;
    }
    return true;
  }
}
