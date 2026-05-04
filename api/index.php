<?php
/**
 * api/index.php — Vercel PHP runtime entry point.
 *
 * Vercel's PHP runtime requires the entry point to live inside /api.
 * This file simply re-roots __DIR__ and delegates to the real index.php
 * one level up, so all existing require/include paths still resolve correctly.
 */

// Change working directory to the project root so every relative path works.
chdir(__DIR__ . '/..');

// Pull in the real application bootstrap.
require __DIR__ . '/../index.php';
