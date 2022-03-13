<?php

namespace Nagasari\Http;

use Closure;

class MiddlewareManager {

    private $layers;

    public function __construct(array $middlewares = [])
    {
        $this->layers = [];

        foreach ($middlewares as $middleware) {
            $this->layers[] = new $middleware;
        }
    }

    public function peel(Request $request, Closure $core): Response
    {
        $coreFunction = $this->createCoreFunction($core);
        $layers = array_reverse($this->layers);

        $completeOnion = array_reduce($layers, function($nextLayer, $layer){
            return $this->createLayer($nextLayer, $layer);
        }, $coreFunction);

        return $completeOnion($request);
    }

    private function createCoreFunction(Closure $core)
    {
        return function($object) use($core) {
            return $core($object);
        };
    }

    private function createLayer($nextLayer, $layer)
    {
        return function($object) use($nextLayer, $layer){
            return $layer->peel($object, $nextLayer);
        };
    }
}