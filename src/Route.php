<?php

namespace Nagasari\Http;

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
        Router::setKey($key, $this->path);
        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }
}