<?php
namespace Core;

class Router {
    protected $routes = [];

    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    public function dispatch($uri, $requestMethod) {
        // Remove query string
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        // Normalize URI (remove trailing slash)
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        // Check if route exists
        if (array_key_exists($requestMethod, $this->routes) && array_key_exists($uri, $this->routes[$requestMethod])) {
            $controllerAction = $this->routes[$requestMethod][$uri];
            $this->execute($controllerAction);
        } else {
            // Basic 404
            http_response_code(404);
            echo "404 Not Found";
        }
    }

    protected function execute($controllerAction) {
        $parts = explode('@', $controllerAction);
        $controllerName = "App\\Controllers\\" . $parts[0];
        $method = $parts[1];

        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                die("Method {$method} not found in {$controllerName}");
            }
        } else {
            die("Controller {$controllerName} not found");
        }
    }
}
