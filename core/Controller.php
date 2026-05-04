<?php
namespace Core;

class Controller {
    protected function render($view, $data = []) {
        View::render($view, $data);
    }

    protected function renderWithoutLayout($view, $data = []) {
        View::renderWithoutLayout($view, $data);
    }

    protected function redirect($url) {
        $base = $this->getBaseUrl();
        $target = $base . '/' . ltrim($url, '/');
        header('Location: ' . $target);
        exit;
    }

    protected function getBaseUrl(): string {
        // Detect scheme — Railway / Vercel always terminate TLS at the proxy,
        // forwarding the original scheme via X-Forwarded-Proto.
        $proto = 'http';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $proto = 'https';
        } elseif (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            $proto = 'https';
        }
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $proto . '://' . $host;
    }

    /** @deprecated Use getBaseUrl() instead */
    protected function getBasePath(): string {
        return '';
    }
}
