<?php

namespace App\Core;

class Router
{
    private static array $routes = [];

    public static function add(string $method, string $path, callable $callback, bool $authRequired = false): void
    {
        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'authRequired' => $authRequired
        ];
    }

    public static function dispatch(): void
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        foreach (self::$routes as $route) {
            $pattern = '#^' . str_replace('/', '\/', preg_replace('/{([a-zA-Z0-9_]+)}/', '([a-zA-Z0-9_]+)', $route['path'])) . '/?$#';

            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);

                if ($route['method'] === $requestMethod) {
                    $params = $matches;

                    if ($route['authRequired']) {
                        $authorizedUser = Auth::authenticate();
                        call_user_func_array($route['callback'], array_merge([$authorizedUser], $params));
                    } else {
                        call_user_func_array($route['callback'], $params);
                    }
                    return;
                }
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
}
