<?php

namespace Nagasari\Http;

class Request
{
    public string $Method;
    public string $Path;
    public array $PathAttribute;
    public array $Query;
    public array $Header;
    public $Content;

    public function Receive(): void
    {
        $this->Method = $_SERVER['REQUEST_METHOD'];
        $this->Path = strtok($_SERVER['REQUEST_URI'], '?&#');
        $this->Query = $_GET;
        
        if (function_exists('getallheaders')) {
            $this->Header = getallheaders();
        } else {
            // just incase the function is not available
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $headerKey = substr($key, 5);
                    $headerKey = ucwords(str_replace('_', '-', strtolower($headerKey)), '-');
                    
                    $this->Header[$headerKey] = $value;
                }
            }
        }

        if (isset($this->Header['Cookie'])) {
            $this->Header['Cookie'] = $_COOKIE;
        }

        $this->Content = '';

        $contentType = isset($this->Header['Content-Type'])
            ? strtok($this->Header['Content-Type'], '; ')
            : '';
   
        switch ($contentType) {
            case 'application/json':
                $this->Content = json_decode(file_get_contents('php://input'), true);
                break;
            case 'application/x-www-form-urlencoded':
                // PHP delete the raw content on POST method
                if ($this->Method === 'POST') {
                    $this->Content = $_POST;
                } else {
                    parse_str(file_get_contents('php://input'), $this->Content);
                }
                break;
            case 'multipart/form-data':
                $formData = new FormData();
                // PHP delete the raw content on POST method but the global FILES is wac
                $this->Content = $this->Method === 'POST'
                    ? $formData->CreatePostMethod()
                    : $formData->CreateNotPostMethod();
                break;
            default:
                $this->Content = file_get_contents('php://input');
                break;
        }
    }
}