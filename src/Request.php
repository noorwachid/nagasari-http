<?php

namespace Nagasari\Http;

class Request
{
    public string $method;
    public string $path;
    public array $pathAttribute;
    public array $query;
    public array $header;
    public $content;

    public function receive(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = strtok($_SERVER['REQUEST_URI'], '?&#');
        $this->query = $_GET;
        
        if (function_exists('getallheaders')) {
            $this->header = getallheaders();
        } else {
            // just incase the function is not available
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $headerKey = substr($key, 5);
                    $headerKey = ucwords(str_replace('_', '-', strtolower($headerKey)), '-');
                    
                    $this->header[$headerKey] = $value;
                }
            }
        }

        if (isset($this->header['Cookie'])) {
            $this->header['Cookie'] = $_COOKIE;
        }

        $this->content = '';

        $contentType = isset($this->header['Content-Type'])
            ? strtok($this->header['Content-Type'], '; ')
            : '';
   
        switch ($contentType) {
            case 'application/json':
                $this->content = json_decode(file_get_contents('php://input'), true);
                break;
            case 'application/x-www-form-urlencoded':
                // PHP delete the raw content on POST method
                if ($this->method === 'POST') {
                    $this->content = $_POST;
                } else {
                    parse_str(file_get_contents('php://input'), $this->body);
                }
                break;
            case 'multipart/form-data':
                $formData = new FormData();
                // PHP delete the raw content on POST method but the global FILES is wac
                $this->content = $this->method === 'POST'
                    ? $formData->composePostMethod()
                    : $formData->composeNotPostMethod();
                break;
            default:
                $this->content = file_get_contents('php://input');
                break;
        }
    }
}