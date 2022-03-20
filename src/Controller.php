<?php

namespace Nagasari\Http;

class Controller 
{
    protected array $middlewares = [];

    protected function Resolve(Request $request): Response
    {
        return new Response('');
    }

    public function Dispatch(Request $request): Response
    {
        // don't bother create middlewares
        if (empty($this->middlewares)) {
            return $this->resolve($request);
        }

        // make the process in one loop instead of using map and reverse
        $middlewares = [];
        $middlewareCount = count($this->middlewares);

        for ($i = $middlewareCount - 1; $i >= 0; --$i) {
            $middlewares[] = new $this->middlewares[$i];
        }

        // create request layers
        $next = array_reduce($middlewares, function($next, $middleware) {
            return function($request) use ($next, $middleware) {
                return $middleware->peel($request, $next);
            };
        }, fn ($request) => $this->resolve($request));

        return $next($request);
    }
}