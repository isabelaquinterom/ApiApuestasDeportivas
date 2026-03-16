<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Middleware VerificarRol
 * Verifica que el usuario autenticado tenga el rol requerido
 * para acceder a una ruta especifica
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-15 23:44 COT
 * @version  1.0
 */
class VerificarRol
{
    /**
     * Verifica el token JWT y el rol del usuario
     * Uso en rutas: middleware('rol:admin') o middleware('rol:usuario')
     */
    public function handle(Request $request, Closure $next, string $rol)
    {
        try {
            // Intentar autenticar al usuario con el token JWT del header
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token invalido o expirado'], 401);
        }

        // Verificar que el rol del usuario coincida con el rol requerido
        if ($user->rol !== $rol) {
            return response()->json(['message' => 'No tienes permiso para esta accion'], 403);
        }

        return $next($request);
    }
}

