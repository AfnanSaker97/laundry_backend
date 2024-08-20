<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
       
        $exceptions->render(function (RouteNotFoundException $e, Request $request) {

            $response = [
                'success' => false,
                'errors' => 'invalid token',
                'status' => 400,
            ];
            return response()->json($response, 400);
        
    });
    })->create();
