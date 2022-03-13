<?php

namespace Nagasari\Http;

class Request
{
    public string $method;
    public string $path;
    public array $pathArgument;
    public array $query;
    public array $header;
    public array|string $body;

    public function receive(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = strtok($_SERVER['REQUEST_URI'], '?&#');
        $this->pathArgument = [];
        $this->query = $_GET;
        $this->header = [];
        $this->body = '';

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $headerKey = substr($key, 5);
                $headerKey = str_replace('_', '-', strtolower($headerKey));
                $this->header[$headerKey] = $value;
            }
        }
        
        if (isset($this->header['content-type'])) {
            $contentType = strtok($this->header['content-type'], '; ');
   
            switch ($contentType) {
                case 'application/json':
                    $this->body = json_decode($this->receiveRawBody(), true);
                    break;
                case 'application/x-www-form-urlencoded':
                    // php will automatically parse 
                    if ($this->method === 'POST') {
                        $this->body = $_POST;
                    } else {
                        parse_str($this->receiveRawBody(), $this->body);
                    }
                    break;
                case 'multipart/form-data':
                    // php will automatically parse but the files is wac
                    if ($this->method === 'POST') {
                        $this->body = RequestFileManager::compose();
                    } else {
                        $this->body = RequestFileManager::composeFromRawBody($this->receiveRawBody());
                    }
                    break;
                default:
                    $this->body = $this->receiveRawBody();
                    break;
            }
        } else {
            $this->body = $this->receiveRawBody();
        }
    }

    private function receiveRawBody(): string
    {
        return file_get_contents('php://input');
    }
}