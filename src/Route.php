<?php

namespace Nagasari\Http;

use Closure;

class Route
{
    public string $key;
    public string $method;
    public string $path;
    public string $controller;

    public function __construct(string $path, string $controller)
    {
        $this->key = '';
        $this->method = '*';
        $this->path = $path;
        $this->controller = $controller;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        self::$map[$this->key] = str_replace(
            // remove all specifier
            [':*}', ':num}', ':alpha}', ':alphanum}'],
            ['}',   '}',     '}',       '}'], 
            $this->path
        );

        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    // ----- // static

    private static array $map = [];
    private static array $vector = [];
    private static string $pathPrefix = '';
    private static string $fallbackController = '';

    public static function get(string $key, array $data = []): string
    {
        $needles = [];
        $values = [];

        foreach ($data as $symbol => $value) {
            $needles[] = '{'.$symbol.'}';
            $values[] = $value;
        }

        return str_replace($needles, $values, self::$map[$key] ?? '');
    }

    public static function set(string $path, string $controller): self
    {
        return self::$vector[] = new Route(self::$pathPrefix.$path, $controller);
    }

    public static function group(string $path, Closure $callback): void
    {
        $pathPrefix = $path;
        self::$pathPrefix .= $path;
        $callback();
        self::$pathPrefix = $pathPrefix;
    }

    public static function setFallback(string $controller): void
    {
        self::$fallbackController = $controller;
    }

    public static function resolve(): void
    {
        $request = new Request();
        $request->receive();

        foreach (self::$vector as $route) {
            $pattern = self::composePatternFromPath($route->path);
            if (preg_match($pattern, $request->path, $matches)) {
                $request->pathAttribute = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

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

    private static function composePatternFromPath($path): string
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

    private static function dispatch(string $controller, Request $request): void
    {
        $controller = new $controller;
        $response = $controller->dispatch($request);
        $response->send();
    }
}