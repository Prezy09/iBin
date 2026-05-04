<?php
namespace Core;

class View {
    public static function render($viewFile, $data = []) {
        extract($data);
        
        $viewPath = __DIR__ . '/../app/Views/' . $viewFile . '.php';
        
        if (file_exists($viewPath)) {
            // Assume we use a layout
            $layoutPath = __DIR__ . '/../app/Views/layout/main.php';
            
            // Capture the view content
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            
            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                echo $content;
            }
        } else {
            die("View {$viewFile} not found!");
        }
    }
    
    // Sometimes we don't want the layout (like login)
    public static function renderWithoutLayout($viewFile, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../app/Views/' . $viewFile . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("View {$viewFile} not found!");
        }
    }
}
