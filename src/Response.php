<?php

namespace Nagasari\Http;

class Response 
{
    public int $statusCode;
    public array $header;
    public $body;

    public function __construct($body = '')
    {
        $this->statusCode = 200;
        $this->header = [];
        $this->body = $body;
    }
}