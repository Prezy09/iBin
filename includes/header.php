<?php $env = require __DIR__ . '/../.env.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($env['APP_NAME']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-light">
<div class="container-fluid">
  <div class="row">
    <aside class="col-12 col-md-2 bg-success text-white min-vh-100 p-3 sidebar">
      <div class="d-flex align-items-center mb-3 sidebar-logos">
        <img src="/assets/img/LOGO 1 SVG.svg" alt="Primary smart waste logo" class="sidebar-logo">
        <img src="/assets/img/LOGO 2 SVG.svg" alt="Secondary smart waste logo" class="sidebar-logo">
      </div>
      <ul class="nav flex-column gap-1">
        <li class="nav-item"><a class="nav-link text-white" href="/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/bins.php">Bins</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/collection.php">Collection</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/analytics.php">Analytics</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/reports.php">Reports</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/settings.php">Settings</a></li>
      </ul>
    </aside>
    <main class="col-12 col-md-10 p-4">
