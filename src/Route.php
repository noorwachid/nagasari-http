<?php

namespace Nagasari\Http;

class Route
{
    public string $key;
    public string $method;
    public string $path;
    public string $controller;

    private static array $map;

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

    public static function compose(string $key, array $data = []): string
    {
        $needles = [];
        $values = [];

        foreach ($data as $key => $value) {
            $needles[] = '{'.$key.'}';
            $values[] = $value;
        }

        return str_replace($needles, $values, self::$map[$key] ?? '');
    }
}