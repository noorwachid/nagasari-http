<?php

namespace Nagasari\Http;

use Closure;
use UnexpectedValueException;

class Server
{
    public function resolve(Closure $resolver): void
    {
        $this->send($resolver($this->receive()));
    }

    public function receive(): Request
    {
        $request = new Request();
        $request->method = $_SERVER['REQUEST_METHOD'];
        $request->path = strtok($_SERVER['REQUEST_URI'], '?&#');
        $request->query = $_GET;
        
        if (function_exists('getallheaders')) {
            $request->header = getallheaders();
        } else {
            // just incase the function is not available
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $headerKey = substr($key, 5);
                    $headerKey = ucwords(str_replace('_', '-', strtolower($headerKey)), '-');
                    
                    $request->header[$headerKey] = $value;
                }
            }
        }

        $request->cookie = $_COOKIE;
        $request->body = '';

        $contentType = isset($request->header['Content-Type'])
            ? strtok($request->header['Content-Type'], '; ')
            : '';
   
        switch ($contentType) {
            case 'application/json':
                $request->body = json_decode(file_get_contents('php://input'), true);
                break;
            case 'application/x-www-form-urlencoded':
                // PHP delete the raw content on POST method
                if ($request->method === 'POST') {
                    $request->body = $_POST;
                } else {
                    parse_str(file_get_contents('php://input'), $request->body);
                }
                break;
            case 'multipart/form-data':
                $formData = new FormData();
                // PHP delete the raw content on POST method but the global FILES is wac
                $request->body = $request->method === 'POST'
                    ? $formData->composePostMethod()
                    : $formData->composeNotPostMethod();
                break;
            default:
                $request->body = file_get_contents('php://input');
                break;
        }

        return $request;
    }

    public function send(Response $response): void
    {
        http_response_code($response->statusCode);

        // if the body is not string we assume you meant to send api
        if (!is_string($response->body)) {
            $response->header['Content-Type'] = 'application/json';
            $response->body = json_encode($response->body);
        }

        foreach ($response->header as $key => $value) {
            header($key.': '.$value);
        }

        echo $response->body;
    }
}