<?php

namespace Nagasari\Http;

class Response 
{
    public int $statusCode;
    public array $header;
    public $content;

    public function __construct($content = '')
    {
        $this->statusCode = 200;
        $this->header = [];
        $this->content = $content;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        // if the content is not string we assume you meant to send api
        if (!is_string($this->content)) {
            $this->header['Content-Type'] = 'application/json; charset=utf-8';
            $this->content = json_encode($this->content);
        }

        foreach ($this->header as $key => $value) {
            header($key.': '.$value);
        }

        echo $this->content;
    }
}