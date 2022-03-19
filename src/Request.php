<?php

namespace Nagasari\Http;

class Request
{
    public string $method;
    public string $path;
    public array $attribute;
    public array $query;
    public array $header;
    public $body;
}