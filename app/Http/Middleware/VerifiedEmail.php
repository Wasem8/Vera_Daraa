<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use \App\Http\Responses\Response;


class VerifiedEmail
{

    public function handle(Request $request, Closure $next): \Illuminate\Http\JsonResponse
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                ! $request->user()->hasVerifiedEmail())) {
            return Response::Success('false','you have not verified your email');
        }
        return $next($request);
    }
}
