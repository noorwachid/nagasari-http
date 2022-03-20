<?php

namespace Nagasari\Http;

class Response 
{
    public int $StatusCode;
    public array $Header;
    public $Content;

    public function __Construct($content = '')
    {
        $this->StatusCode = 200;
        $this->Header = [];
        $this->Content = $content;
    }

    public function Send(): void
    {
        http_response_code($this->StatusCode);

        // if the content is not string we assume you meant to send api
        if (!is_string($this->Content)) {
            $this->Header['Content-Type'] = 'application/json; charset=utf-8';
            $this->Content = json_encode($this->Content);
        }

        foreach ($this->Header as $key => $value) {
            header($key.': '.$value);
        }

        echo $this->Content;
    }
}