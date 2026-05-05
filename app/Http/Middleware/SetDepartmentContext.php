<?php

namespace App\Http\Middleware;

use App\Services\DepartmentContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetDepartmentContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! DepartmentContext::currentId()) {
            DepartmentContext::initializeForUser($user);
        }

        return $next($request);
    }
}
