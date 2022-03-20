<?php

namespace Nagasari\Http;

use Closure;

class Middleware 
{
    public function Peel(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

