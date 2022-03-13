<?php

namespace Nagasari\Http;

use Closure;

class Router 
{
    private static array $routes = [];
    private static array $pathMap = [];
    private static string $groupPath = '';
    private static string $fallbackController = '';

    public static function set(string $path, string $controller): Route
    {
        $route = new Route($path, $controller);
        self::$routes[] = $route;
        return $route;
    }

    public static function group(string $path, Closure $callback): void
    {
        $oldGroupPath = $path;
        self::$groupPath .= $path;
        $callback();
        self::$groupPath = $oldGroupPath;
    }

    public static function setKey($key, $path): void
    {
        self::$pathMap[$key] = $path;
    }

    public static function setFallback(string $controller): void
    {
        self::$fallbackController = $controller;
    }

    public static function resolve(): void
    {
        $request = new Request();
        $request->receive();

        foreach (self::$routes as $route) {
            $pattern = self::composePatternFromPath($route->path);
            if (preg_match($pattern, $request->path, $matches)) {
                $request->pathArgument = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if ($route->method === $request->method || 
                    $route->method === '*') {
                    self::dispatch($route->controller, $request);
                    return;
                }
            }
        }

        self::dispatch(self::$fallbackController, $request);
        return;
    }

    public static function compose(string $key, array $data = []): string
    {
        $needles = [];
        $values = [];

        foreach ($data as $key => $value) {
            $needles[] = '{'.$key.'}';
            $values[] = $value;
        }

        return str_replace($needles, $values, self::$pathMap[$key] ?? '');
    }

    private static function composePatternFromPath($path) {
        // replace / and {name} to valid regex
        $path = str_replace(['/', '{', '}'], ['\/', '(?<', '>[^\/]+)'], $path);
        $path = '/^'.$path.'$/';

        return $path;
    }

    private static function dispatch(string $controller, Request $request): void
    {
        $controller = new $controller;
        $middlewareManager = new MiddlewareManager($controller->middlewares);
        $response = $middlewareManager->peel($request, function ($request) use ($controller) 
        {
            return $controller->resolve($request);
        });
        $response->send();
    }
}