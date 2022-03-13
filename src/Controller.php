<?php

namespace Nagasari\Http;

class Controller 
{
    public array $middlewares = [];

    public function resolve(Request $request): Response
    {
        return new Response('');
    }
}