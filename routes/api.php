<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ApuestaController;

/**
 * Rutas de la API
 *
 * Rutas publicas:   No requieren token JWT
 * Rutas privadas:   Requieren header Authorization: Bearer TOKEN
 * Rutas de admin:   Requieren token JWT con rol admin
 * Rutas de usuario: Requieren token JWT con rol usuario
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-15 23:44 COT
 * @version  1.0
 */

// ==========================================
// RUTAS PUBLICAS - No requieren token JWT
// ==========================================
Route::post('/register',   [AuthController::class, 'register']);   // Registro de usuario
Route::post('/login',      [AuthController::class, 'login']);      // Login paso 1 - envia OTP
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']); // Login paso 2 - verifica OTP y entrega JWT

// ==========================================
// RUTAS PROTEGIDAS - Requieren token JWT valido
// ==========================================
Route::middleware(['jwt.auth'])->group(function () {

    // --- Autenticacion ---
    Route::post('/logout', [AuthController::class, 'logout']); // Cerrar sesion
    Route::get('/me',      [AuthController::class, 'me']);     // Ver perfil propio

    // --- Eventos (cualquier usuario autenticado puede verlos) ---
    Route::get('/eventos',      [EventoController::class, 'listar']); // Listar todos los eventos
    Route::get('/eventos/{id}', [EventoController::class, 'ver']);    // Ver un evento especifico

    // --- Apuestas (solo usuarios) ---
    Route::middleware(['rol:usuario'])->group(function () {
        Route::post('/apuestas',             [ApuestaController::class, 'apostar']);     // Realizar apuesta
        Route::get('/apuestas/mis',          [ApuestaController::class, 'misApuestas']); // Ver mis apuestas
        Route::post('/apuestas/{id}/cobrar', [ApuestaController::class, 'cobrar']);      // Cobrar apuesta ganada
    });

    // ==========================================
    // RUTAS SOLO PARA ADMIN
    // ==========================================
    Route::middleware(['rol:admin'])->group(function () {
        Route::post('/eventos',          [EventoController::class, 'crear']);          // Crear evento con cuotas
        Route::get('/admin/apuestas',    [ApuestaController::class, 'todasLasApuestas']); // Ver todas las apuestas
    });
});

