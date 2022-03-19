<?php

namespace Nagasari\Http;

use Closure;

class Router 
{
    private array $routes = [];
    private string $groupPath = '';
    private string $fallbackController = '';

    public function add(string $path, string $controller): Route
    {
        $route = new Route($this->groupPath.$path, $controller);
        $this->routes[] = $route;
        return $route;
    }

    public function group(string $path, Closure $callback): void
    {
        $oldGroupPath = $path;
        $this->groupPath .= $path;
        $callback();
        $this->groupPath = $oldGroupPath;
    }

    public function setKey($key, $path): void
    {
        $this->pathMap[$key] = $path;
    }

    public function setFallback(string $controller): void
    {
        $this->fallbackController = $controller;
    }

    public function resolve(Request $request): Response
    {
        foreach ($this->routes as $route) {
            $pattern = self::composePatternFromPath($route->path);
            if (preg_match($pattern, $request->path, $matches)) {
                $request->attribute = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if ($route->method === $request->method || 
                    $route->method === '*') {
                    
                    return $this->dispatch($route->controller, $request);
                }
            }
        }

        return $this->dispatch($this->fallbackController, $request);
    }

    private function composePatternFromPath($path): string
    {
        // replace / and {name} to valid regex
        $path = str_replace(
            ['/',  '{',   ':*}',  ':num}',      ':alpha}',       ':alphanum}',       '}'], 
            ['\/', '(?<', '>.*)', '>[0-9\.]+)', '>[A-Za-z-_]+)', '>[A-Za-z0-9-_\.]', '>[^\/]+)'], 
            $path
        );
        $path = '/^'.$path.'$/';

        return $path;
    }

    private function dispatch(string $controller, Request $request): Response 
    {
        $controller = new $controller;

        return $controller->dispatch($request);
    }
}