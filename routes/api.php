<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ApuestaController;
use App\Http\Controllers\AdminController;

/**
 * Rutas principales de la API
 *
 * Estructura general:
 * - Rutas publicas
 * - Rutas protegidas con JWT
 * - Rutas de usuario
 * - Rutas de administrador
 *
 * Seguridad:
 * - jwt.auth -> valida token JWT
 * - rol:usuario -> solo usuarios normales
 * - rol:admin -> solo administradores
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-15 23:44 COT
 * @version  1.0
 */

/*
|--------------------------------------------------------------------------
| RUTAS PUBLICAS
|--------------------------------------------------------------------------
| No requieren token JWT
|--------------------------------------------------------------------------
*/

// Registro de usuario
Route::post('/register', [AuthController::class, 'register']);

// Login paso 1: valida credenciales y envia OTP
Route::post('/login', [AuthController::class, 'login']);

// Login paso 2: verifica OTP y entrega JWT
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);


/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
| Requieren token JWT valido
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTENTICACION
    |--------------------------------------------------------------------------
    */

    // Cerrar sesion
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ver perfil del usuario autenticado
    Route::get('/me', [AuthController::class, 'me']);


    /*
    |--------------------------------------------------------------------------
    | EVENTOS
    |--------------------------------------------------------------------------
    | Cualquier usuario autenticado puede ver eventos
    |--------------------------------------------------------------------------
    */

    // Listar todos los eventos
    Route::get('/eventos', [EventoController::class, 'listar']);

    // Ver detalle de un evento
    Route::get('/eventos/{id}', [EventoController::class, 'ver']);


    /*
    |--------------------------------------------------------------------------
    | RUTAS SOLO PARA USUARIO
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:usuario'])->group(function () {

        // Realizar una apuesta
        Route::post('/apuestas', [ApuestaController::class, 'apostar']);

        // Ver mis apuestas
        Route::get('/apuestas/mis', [ApuestaController::class, 'misApuestas']);

        // Cobrar una apuesta ganada
        Route::post('/apuestas/{id}/cobrar', [ApuestaController::class, 'cobrar']);
    });


    /*
    |--------------------------------------------------------------------------
    | RUTAS SOLO PARA ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:admin'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | EVENTOS
        |--------------------------------------------------------------------------
        */

        // Crear evento con sus cuotas
        Route::post('/eventos', [EventoController::class, 'crear']);

        /*
        |--------------------------------------------------------------------------
        | RESULTADOS
        |--------------------------------------------------------------------------
        */

        // Simular resultado de un evento
        Route::post('/eventos/{id}/resultado', [AdminController::class, 'simularResultado']);

        /*
        |--------------------------------------------------------------------------
        | USUARIOS
        |--------------------------------------------------------------------------
        */

        // Ver todos los usuarios
        Route::get('/admin/usuarios', [AdminController::class, 'listarUsuarios']);

        // Ajustar saldo de un usuario
        Route::put('/admin/usuarios/{id}/saldo', [AdminController::class, 'ajustarSaldo']);

        /*
        |--------------------------------------------------------------------------
        | APUESTAS
        |--------------------------------------------------------------------------
        */

        // Ver todas las apuestas del sistema
        Route::get('/admin/apuestas', [ApuestaController::class, 'todasLasApuestas']);
    });
});
