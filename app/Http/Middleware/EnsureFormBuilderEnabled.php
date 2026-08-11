<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFormBuilderEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('features.form_builder'), 404);

        return $next($request);
    }
}
