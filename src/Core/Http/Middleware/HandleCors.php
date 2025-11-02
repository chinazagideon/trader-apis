<?php

namespace App\Core\Http\Middleware;

use Closure;
use Fruitcake\Cors\CorsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    public function __construct(private CorsService $cors)
    {
    }

    /**
     * hande reques
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->cors->isCorsRequest($request)) {
            if ($this->cors->isPreflightRequest($request)) {
                return $this->cors->handlePreflightRequest($request);
            }
        }

        $response = $next($request);

        if ($this->cors->isCorsRequest($request)) {
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }
}
