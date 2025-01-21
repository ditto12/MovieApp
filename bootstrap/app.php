<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\CheckPaymentStatus;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
  
       $middleware->alias([
            'admin.auth' => App\Http\Middleware\AdminAuthenticate::class,
            'admin.guest' => App\Http\Middleware\AdminRedirect::class,
            'check.payment'=> CheckPaymentStatus::class,

       ]);
       
        $middleware->redirectTo(
            guests: '/login',
            users: '/users/dashboard',
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {

    })->create();
