# Raspberry Pi → PHP → Firebase → iBin UI

This document explains how the current web app already handles bin telemetry, and how to wire a Raspberry Pi (or any IoT node) into that flow.

## 1. Web stack overview (already implemented)

```
Raspberry Pi ──HTTP POST──▶ api/sensor_ingest.php ──▶ Firebase /bins/{bin_id}
                                                   │
                                                   └─▶ Web pages (bins.php, dashboard.php, etc.)
```

### Firebase credentials

- `.env.php` (or `.env.example.php`) provides `FIREBASE_DATABASE_URL` and `FIREBASE_CREDENTIALS`.
- `config/firebase.php` loads the service account, generates auth tokens, and exposes helpers:
  - `firebase_db_get($path, $query = [])`
  - `firebase_db_patch($path, $payload)`
  - `firebase_is_ready()`

### Database abstraction (`includes/db.php`)

- `db_get_bins()` reads `/bins` from Firebase and normalizes fields (bin id, fill levels, GPS, timestamps, etc.).
- `db_save_bins()` normalizes payloads and writes them back (`firebase_db_patch('bins', $payload)`).
- `find_bin($id)` fetches `/bins/{id}` (with a fallback to sample JSON) and normalizes the result.
- `db_normalize_bin()` is the canonical field shaper (aliases last_ping ↔ lastPing, fill_bio ↔ fillBio, etc.).

### Sensor ingest API (`api/sensor_ingest.php`)

This is the endpoint that the Raspberry Pi should call. It:

1. Accepts JSON or form-data:
   - Required: `bin_id`
   - Optional but supported: `bio`, `rec`, `res`, `battery`, `lat`, `lng`, `status`, `name`, `address`, `type`, etc.
2. Looks up the bin via `find_bin($bin_id)` (or seeds a default template).
3. Updates:
   - Compartment percentages (`fill_bio`, etc. plus `compartments.biodegradable`, etc.).
   - Battery (`battery`, `battery_level`, `batteryLevel`).
   - GPS (`gps.lat`, `gps.lng`, plus location aliases).
   - Heartbeat timestamps (`last_ping`, `last_ping_at`, `lastPing`, `last_updated`).
4. Saves the result with `db_save_bins([$target])`.
5. Returns JSON `{ "ok": true, "bin": {...} }`.

Every call therefore acts as a “heartbeat” and telemetry update for the Pi.

### Web UI heartbeat usage (example: `bins.php`)

`bins.php` calls `db_get_bins()` and then:

```php
$latestPingTs = null;
foreach ($bins as $candidate) {
    $pingValue = $candidate['last_ping']
        ?? $candidate['lastPing']
        ?? $candidate['last_ping_at']
        ?? null;
    // track newest $latestPingTs…
}
$piConnected = $latestPingTs !== null && (time() - $latestPingTs) <= 300;
```

That drives the “Raspberry Pi Online/Offline” chip in the UI. Any heartbeat within 5 minutes keeps the status “Online”.

## 2. Raspberry Pi client script (example)

Below is a Python reference implementation that can run on each Raspberry Pi. Replace the placeholder code that reads sensors with real hardware calls.

```python
#!/usr/bin/env python3
"""
Heartbeat/sensor publisher for iBin.

Requirements:
  pip install requests
"""
import time
import requests

API_URL = "https://your-server.com/api/sensor_ingest.php"  # TODO: update
BIN_ID = "hm_talaban_market"  # unique per unit
AUTH_TOKEN = "eco_live_0f9c12"  # optional: add as ?token=... or header, see below


def read_sensors():
    """
    Replace this stub with real sensor/GPS/BMS reads.
    Must return percentages in the 0–100 range and lat/lng in decimal degrees.
    """
    fill_bio = 45.0   # percent
    fill_rec = 30.0   # percent
    fill_res = 12.5   # percent
    battery = 82.0    # percent
    lat = 10.1017
    lng = 122.8664
    return {
        "bio": fill_bio,
        "rec": fill_rec,
        "res": fill_res,
        "battery": battery,
        "lat": lat,
        "lng": lng,
    }


def make_payload():
    payload = {
        "bin_id": BIN_ID,
        "status": "Online",
        "type": "Market",  # or Government, Residential, etc.
        # "name": "Talaban Public Market Bin",
        # "address": "Talaban Public Market, Himamaylan",
    }
    payload.update(read_sensors())
    return payload


def send_heartbeat():
    body = make_payload()
    params = {}
    headers = {"User-Agent": "iBinPi/1.0"}
    if AUTH_TOKEN:
        # Option A: query param (easier)
        params["token"] = AUTH_TOKEN
        # Option B: custom header (if you add server-side support)
        # headers["X-IBIN-TOKEN"] = AUTH_TOKEN
    resp = requests.post(API_URL, json=body, params=params, headers=headers, timeout=10)
    resp.raise_for_status()
    print("Heartbeat OK:", resp.json())


def main():
    while True:
        try:
            send_heartbeat()
        except Exception as exc:
            print("Heartbeat failed:", exc)
        time.sleep(60)  # send every 60 seconds


if __name__ == "__main__":
    main()
```

### Deployment tips

1. **Install requirements**

```bash
sudo apt update
sudo apt install python3 python3-pip -y
pip3 install --upgrade requests
```

2. **Create a systemd service** (`/etc/systemd/system/ibin-heartbeat.service`)

```ini
[Unit]
Description=iBin heartbeat sender
After=network-online.target

[Service]
Type=simple
ExecStart=/usr/bin/python3 /home/pi/ibin_sender.py
Restart=always
User=pi
Environment=PYTHONUNBUFFERED=1

[Install]
WantedBy=multi-user.target
```

Enable + start:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now ibin-heartbeat.service
sudo journalctl -u ibin-heartbeat.service -f
```

3. **Optional security** — The API currently trusts any caller. To lock it down:

- Add a `token` parameter check to `api/sensor_ingest.php`, verifying against a list stored in Firebase or `.env.php`.
- Alternatively require HTTP Basic Auth or Mutual TLS.

4. **Monitoring**

- If no heartbeat is received for >5 minutes, `bins.php` will show “Raspberry Pi: Offline” and the bin’s `last_ping` stops updating.
- Use `journalctl` on the Pi and server logs (Apache/Nginx/PHP) for debugging.

## 3. Testing checklist

1. Hit `https://your-server.com/api/sensor_ingest.php` manually with curl or Postman to confirm `{ ok: true }`.
2. Run the Python loop on the Pi for a few minutes.
3. Load `bins.php` and verify:
   - The new bin appears (if it was already saved in Firebase).
   - Fill levels, battery, coordinates update.
   - “Raspberry Pi” chip shows “Online”.
4. Stop the Pi script for >5 minutes and confirm the UI switches to “Offline”.

That’s it—the heartbeat you already built in PHP is ready; just wire up the Pi to call `api/sensor_ingest.php` on a schedule. Let me know if you need the script adapted to specific hardware (HC-SR04 ultrasonic, INA219 battery sensor, GPS HAT, etc.).
