<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * Configuracion principal de la aplicacion
 * Registro de middlewares y rutas
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-15 23:44 COT
 * @version  1.0
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // Middleware para verificar el rol del usuario (admin o usuario)
            'rol'      => \App\Http\Middleware\VerificarRol::class,
            // Middleware de JWT para proteger rutas que requieren autenticacion
            'jwt.auth' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


