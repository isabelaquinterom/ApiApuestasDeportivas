<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Middleware VerificarRol
 * 
 * Este middleware valida que el usuario autenticado
 * tenga el rol requerido para acceder a una ruta.
 *
 * Se usa en las rutas asi:
 * 
 * middleware('rol:admin')
 * middleware('rol:usuario')
 *
 * Flujo:
 * 1. Lee el token JWT del header Authorization
 * 2. Autentica el usuario
 * 3. Verifica su rol
 * 4. Permite o bloquea el acceso
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-15
 * @version  1.0
 */
class VerificarRol
{
    /**
     * Metodo principal del middleware
     */
    public function handle(Request $request, Closure $next, string $rol)
    {
        try {

            // Obtener usuario desde el token JWT
            $user = JWTAuth::parseToken()->authenticate();

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Token invalido o expirado'
            ], 401);
        }

        /**
         * Verificar que el rol del usuario
         * coincida con el rol requerido
         */
        if ($user->rol !== $rol) {

            return response()->json([
                'message' => 'No tienes permiso para esta accion'
            ], 403);
        }

        return $next($request);
    }
}

