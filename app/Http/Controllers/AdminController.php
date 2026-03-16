<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Apuesta;
use App\Models\Resultado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Administracion
 * Maneja las acciones exclusivas del administrador
 *
 * Funciones principales:
 * - Simular resultados de eventos
 * - Procesar apuestas ganadas y perdidas
 * - Ajustar saldo de usuarios
 * - Listar usuarios del sistema
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 05:30 COT
 * @version  1.0
 */
class AdminController extends Controller
{
    /**
     * Metodo privado para enviar correos sin validacion estricta SSL
     * Se usa para evitar problemas de certificados en Windows con Laragon
     */
    private function enviarCorreo($para, $asunto, $mensaje)
    {
        $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
            'smtp.gmail.com',
            587,
            false
        );

        $transport->setUsername(env('MAIL_USERNAME'));
        $transport->setPassword(env('MAIL_PASSWORD'));
        $transport->getStream()->setStreamOptions([
            'ssl' => [
                'allow_self_signed' => true,
                'verify_peer'       => false,
                'verify_peer_name'  => false,
            ]
        ]);

        $mailer = new \Symfony\Component\Mailer\Mailer($transport);

        $email = (new \Symfony\Component\Mime\Email())
            ->from(env('MAIL_FROM_ADDRESS'))
            ->to($para)
            ->subject($asunto)
            ->text($mensaje);

        $mailer->send($email);
    }

    /**
     * ADMIN
     * Simular el resultado de un evento deportivo
     *
     * Endpoint: POST /api/eventos/{id}/resultado
     *
     * Flujo:
     * 1. Validar el resultado recibido
     * 2. Buscar el evento
     * 3. Verificar que no este finalizado
     * 4. Guardar el resultado
     * 5. Marcar el evento como finalizado
     * 6. Procesar apuestas pendientes
     * 7. Marcar apuestas como ganadas o perdidas
     * 8. Notificar a los usuarios por correo
     */
    public function simularResultado(Request $request, $id)
    {
        // Validar que el resultado venga correctamente
        $request->validate([
            'resultado' => 'required|in:local,empate,visitante',
        ]);

        // Buscar el evento por ID
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => 'Evento no encontrado'
            ], 404);
        }

        // Verificar que el evento no haya sido finalizado antes
        if ($evento->estado === 'finalizado') {
            return response()->json([
                'message' => 'Este evento ya fue finalizado'
            ], 400);
        }

        // Procesar resultado y apuestas dentro de una transaccion
        DB::transaction(function () use ($evento, $request) {

            /**
             * Paso 1
             * Guardar el resultado final del evento
             */
            Resultado::create([
                'evento_id' => $evento->id,
                'resultado' => $request->resultado,
            ]);

            /**
             * Paso 2
             * Marcar el evento como finalizado
             */
            $evento->estado = 'finalizado';
            $evento->save();

            /**
             * Paso 3
             * Buscar apuestas pendientes del evento
             */
            $apuestas = Apuesta::where('evento_id', $evento->id)
                ->where('estado', 'pendiente')
                ->with('usuario')
                ->get();

            /**
             * Paso 4
             * Procesar cada apuesta
             */
            foreach ($apuestas as $apuesta) {

                // Si el tipo apostado coincide con el resultado, la apuesta gana
                if ($apuesta->tipo_apuesta === $request->resultado) {
                    $apuesta->estado = 'ganada';
                    $apuesta->save();

                    // Intentar enviar correo de apuesta ganada
                    try {
                        $this->enviarCorreo(
                            $apuesta->usuario->email,
                            'Ganaste tu apuesta! - Apuestas Deportivas',
                            "Felicitaciones! Tu apuesta ha ganado!\n\n" .
                            "Evento: {$evento->equipo_local} vs {$evento->equipo_visitante}\n" .
                            "Tipo apostado: {$apuesta->tipo_apuesta}\n" .
                            "Ganancia: {$apuesta->ganancia}\n\n" .
                            "Puedes cobrar tu ganancia desde la app."
                        );
                    } catch (\Exception $e) {
                        // Si falla el correo, no se detiene el proceso
                    }
                } else {
                    // Si no coincide, la apuesta pierde
                    $apuesta->estado = 'perdida';
                    $apuesta->save();

                    // Intentar enviar correo de apuesta perdida
                    try {
                        $this->enviarCorreo(
                            $apuesta->usuario->email,
                            'Resultado de tu apuesta - Apuestas Deportivas',
                            "Tu apuesta ha perdido.\n\n" .
                            "Evento: {$evento->equipo_local} vs {$evento->equipo_visitante}\n" .
                            "Tipo apostado: {$apuesta->tipo_apuesta}\n" .
                            "Monto apostado: {$apuesta->monto}\n\n" .
                            "Suerte en la proxima!"
                        );
                    } catch (\Exception $e) {
                        // Si falla el correo, no se detiene el proceso
                    }
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
     * ADMIN
     * Ajustar el saldo de un usuario
     *
     * Endpoint: PUT /api/admin/usuarios/{id}/saldo
     */
    public function ajustarSaldo(Request $request, $id)
    {
        // Validar saldo recibido
        $request->validate([
            'saldo' => 'required|numeric|min:0',
        ]);

        // Buscar usuario
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Guardar saldo anterior para mostrar el cambio
        $saldo_anterior = $user->saldo;

        // Actualizar saldo
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
     * ADMIN
     * Ver todos los usuarios con rol usuario
     *
     * Endpoint: GET /api/admin/usuarios
     */
    public function listarUsuarios()
    {
        $usuarios = User::where('rol', 'usuario')->get();

        return response()->json([
            'message' => 'Listado de usuarios',
            'data'    => $usuarios
        ]);
    }
}

