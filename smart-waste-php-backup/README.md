# Smart Waste (PHP + Bootstrap Demo)

This project now reads and writes everything through your Firebase Realtime Database: user accounts, smart-bin telemetry, and route-planning data all stay in sync with Firebase via secure service-account tokens.

## Quick Start (XAMPP)
1. Copy the project folder into `htdocs` (example: `C:\xampp\htdocs\smart-waste-php`).  
2. Duplicate `.env.example.php` as `.env.php`.
3. In Firebase Console → Project Settings → Service Accounts, click **Generate new private key**, download the JSON, and place it at `config/firebase-credentials.json` (or change `FIREBASE_CREDENTIALS` to wherever you store the file).
4. Edit `.env.php`:
   - Set `FIREBASE_DATABASE_URL` to your instance, e.g. `https://ibin-2d590-default-rtdb.asia-southeast1.firebasedatabase.app`.
   - Verify `FIREBASE_CREDENTIALS` points to the JSON you downloaded. (If you cannot keep the JSON on disk, leave it blank and paste the `client_email` + `private_key` into `FIREBASE_CLIENT_EMAIL` / `FIREBASE_PRIVATE_KEY`.)
5. Start Apache/PHP in XAMPP and open `http://localhost/smart-waste-php/`.

📝 All authentication (register/login) now happens inside the `/users` node of Firebase, so you do not need a MySQL server at all unless you want a legacy backup.

## Data Flow Overview
- `config/firebase.php` signs Google OAuth tokens with your service account (no deprecated database secrets). Tokens are cached until they expire.
- `includes/db.php` pulls `bins` from Firebase and, if Firebase is unavailable, falls back to the bundled JSON files (now empty so you only see live data).
- API endpoints:
  - `api/bins.php` → returns `{ bins: [...], count, source }` straight from Firebase.
  - `api/sensor_ingest.php` → upserts each bin (fill levels, battery, GPS, metadata) back into Firebase.
  - `api/route.php` → computes pickup order from the live bins dataset.
- Front-end pages (Dashboard widgets, Bins grid, Collection optimizer) already poll those endpoints, so they immediately reflect Firebase updates.

## Sensor → Server (HTTP)
Existing devices can keep POSTing to `/api/sensor_ingest.php`:

```bash
curl -X POST http://<your-pc>/smart-waste-php/api/sensor_ingest.php \
  -H "Content-Type: application/json" \
  -d '{"bin_id":"YOUR_BIN_ID","bio":70,"rec":20,"res":50,"battery":90,"lat":10.0932,"lng":122.8695}'
```

Values are clamped to 0–100, GPS + metadata are stored under `bins/<bin_id>`, and the endpoint responds with the updated record.

## Optional: Legacy MySQL
`config/db.php` and the legacy `.env` keys remain if you still want to mirror data into MySQL, but the application no longer reads from that stack. Firebase is the single source of truth by default.

## UI Notes
- Chart.js + OpenStreetMap integrations are unchanged.
- Dashboard stats poll `api/bins.php` on an interval; if Firebase is offline, the bundled data is empty so the UI stays blank until new telemetry arrives.
- The Collection planner uses haversine distance between live GPS coordinates supplied by Firebase.
