<?php

namespace Nagasari\Http;

use Closure;

class Route
{
    public string $Key;
    public string $Method;
    public string $Path;
    public string $Controller;

    public function __Construct(string $path, string $controller)
    {
        $this->Key = '';
        $this->Method = '*';
        $this->Path = $path;
        $this->Controller = $controller;
    }

    public function SetKey(string $key): self
    {
        $this->Key = $key;

        self::$map[$this->Key] = str_replace(
            // remove all specifier
            [':*}', ':num}', ':alpha}', ':alphanum}'],
            ['}',   '}',     '}',       '}'], 
            $this->Path
        );

        return $this;
    }

    public function SetMethod(string $method): self
    {
        $this->Method = $method;

        return $this;
    }

    // ----- // static

    private static array $map = [];
    private static array $vector = [];
    private static string $pathPrefix = '';
    private static string $fallbackController = '';

    public static function Get(string $key, array $data = []): string
    {
        $needles = [];
        $values = [];

        foreach ($data as $symbol => $value) {
            $needles[] = '{'.$symbol.'}';
            $values[] = $value;
        }

        return str_replace($needles, $values, self::$map[$key] ?? '');
    }

    public static function Set(string $path, string $controller): self
    {
        return self::$vector[] = new Route(self::$pathPrefix.$path, $controller);
    }

    public static function Group(string $path, Closure $callback): void
    {
        $pathPrefix = $path;
        self::$pathPrefix .= $path;
        $callback();
        self::$pathPrefix = $pathPrefix;
    }

    public static function SetFallback(string $controller): void
    {
        self::$fallbackController = $controller;
    }

    public static function Resolve(): void
    {
        $request = new Request();
        $request->Receive();

        foreach (self::$vector as $route) {
            $pattern = self::CreatePatternFromPath($route->Path);
            if (preg_match($pattern, $request->Path, $matches)) {
                $request->PathAttribute = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if ($route->Method === $request->Method || 
                    $route->Method === '*') {
                    
                    self::Dispatch($route->Controller, $request);
                    return;
                }
            }
        }

        self::Dispatch(self::$fallbackController, $request);
        return;
    }

    private static function CreatePatternFromPath(string $path): string
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

    private static function Dispatch(string $controller, Request $request): void
    {
        $controller = new $controller;
        $response = $controller->Dispatch($request);
        $response->Send();
    }
}