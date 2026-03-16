<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Apuesta;
use App\Models\Resultado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Controlador de Administracion
 * Maneja las acciones exclusivas del administrador
 *
 * Acciones del Admin: simular resultados, ajustar saldo, ver usuarios
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-15 23:44 COT
 * @version  1.0
 */
class AdminController extends Controller
{
    /**
     * ADMIN - Simular el resultado de un evento
     * Procesa todas las apuestas pendientes del evento
     * Notifica a cada usuario si gano o perdio
     * Usa transaccion para procesar todo atomicamente
     * Endpoint: POST /api/eventos/{id}/resultado
     */
    public function simularResultado(Request $request, $id)
    {
        // Validar que el resultado sea uno de los valores permitidos
        $request->validate([
            'resultado' => 'required|in:local,empate,visitante',
        ]);

        // Buscar el evento
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }

        // Verificar que el evento no haya sido finalizado antes
        if ($evento->estado === 'finalizado') {
            return response()->json(['message' => 'Este evento ya fue finalizado'], 400);
        }

        // Usar transaccion para procesar todas las apuestas atomicamente
        // Si algo falla en el medio, todo se revierte
        DB::transaction(function () use ($evento, $request) {

            // Paso 1: Guardar el resultado del evento
            Resultado::create([
                'evento_id' => $evento->id,
                'resultado' => $request->resultado,
            ]);

            // Paso 2: Marcar el evento como finalizado
            $evento->estado = 'finalizado';
            $evento->save();

            // Paso 3: Obtener todas las apuestas pendientes de este evento
            $apuestas = Apuesta::where('evento_id', $evento->id)
                                ->where('estado', 'pendiente')
                                ->with('usuario')
                                ->get();

            // Paso 4: Procesar cada apuesta segun el resultado
            foreach ($apuestas as $apuesta) {

                if ($apuesta->tipo_apuesta === $request->resultado) {

                    // La apuesta gano - marcarla como ganada
                    $apuesta->estado = 'ganada';
                    $apuesta->save();

                    // Notificar al usuario que gano
                    Mail::raw(
                        "Felicitaciones! Tu apuesta ha ganado!\n\nEvento: {$evento->equipo_local} vs {$evento->equipo_visitante}\nTipo apostado: {$apuesta->tipo_apuesta}\nGanancia: {$apuesta->ganancia}\n\nPuedes cobrar tu ganancia desde la app.",
                        function ($message) use ($apuesta) {
                            $message->to($apuesta->usuario->email)
                                    ->subject('Ganaste tu apuesta! - Apuestas Deportivas');
                        }
                    );

                } else {

                    // La apuesta perdio - marcarla como perdida
                    $apuesta->estado = 'perdida';
                    $apuesta->save();

                    // Notificar al usuario que perdio
                    Mail::raw(
                        "Tu apuesta ha perdido.\n\nEvento: {$evento->equipo_local} vs {$evento->equipo_visitante}\nTipo apostado: {$apuesta->tipo_apuesta}\nMonto apostado: {$apuesta->monto}\n\nSuerte en la proxima!",
                        function ($message) use ($apuesta) {
                            $message->to($apuesta->usuario->email)
                                    ->subject('Resultado de tu apuesta - Apuestas Deportivas');
                        }
                    );
                }
            }
        });

        return response()->json([
            'message'   => 'Resultado simulado y apuestas procesadas correctamente',
            'evento'    => $evento->load('resultado'),
            'resultado' => $request->resultado
        ]);
    }

    /**
     * ADMIN - Ajustar el saldo de un usuario
     * Permite al admin agregar o modificar el saldo de cualquier usuario
     * Endpoint: PUT /api/admin/usuarios/{id}/saldo
     */
    public function ajustarSaldo(Request $request, $id)
    {
        // Validar que el saldo sea un numero positivo
        $request->validate([
            'saldo' => 'required|numeric|min:0',
        ]);

        // Buscar el usuario
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Guardar el saldo anterior para mostrarlo en la respuesta
        $saldo_anterior = $user->saldo;

        // Actualizar el saldo del usuario
        $user->saldo = $request->saldo;
        $user->save();

        return response()->json([
            'message'        => 'Saldo actualizado correctamente',
            'usuario'        => $user->nombre,
            'saldo_anterior' => $saldo_anterior,
            'saldo_nuevo'    => $user->saldo
        ]);
    }

    /**
     * ADMIN - Ver todos los usuarios registrados
     * Retorna solo los usuarios con rol usuario (no admins)
     * Endpoint: GET /api/admin/usuarios
     */
    public function listarUsuarios()
    {
        // Traer solo usuarios con rol usuario
        $usuarios = User::where('rol', 'usuario')->get();
        return response()->json($usuarios);
    }
}

