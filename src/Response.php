<?php

namespace Nagasari\Http;

class Response 
{
    public int $statusCode;
    public array $header;
    public array|string $body;

    public function __construct(array|string $body)
    {
        $this->statusCode = 200;
        $this->header = [];
        $this->body = $body;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        if (!is_string($this->body)) {
            $this->header['content-type'] = 'application/json';
            $this->body = json_encode($this->body);
        }

        foreach ($this->header as $key => $value) {
            header($key.': '.$value);
        }

        echo $this->body;
    }
}