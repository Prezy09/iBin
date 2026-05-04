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
        // Ensure redirect uses correct base path if needed
        $basePath = $this->getBasePath();
        $target = $basePath . '/' . ltrim($url, '/');
        header("Location: " . $target);
        exit;
    }

    protected function getBasePath() {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
            return '';
        }
        return rtrim($scriptDir, '/');
    }
}
