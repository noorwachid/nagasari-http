<?php

namespace Nagasari\Http;

use Closure;

class Middleware 
{
    public function peel(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}