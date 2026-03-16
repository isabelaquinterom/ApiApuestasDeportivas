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
 * Acciones del Admin: simular resultados, ajustar saldo, ver usuarios
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 05:30 COT
 * @version  1.0
 */
class AdminController extends Controller
{
    /**
     * Metodo privado para enviar correos sin SSL verification
     * Soluciona problema de certificados en Windows con Laravel
     */
    private function enviarCorreo($para, $asunto, $mensaje)
    {
        $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport('smtp.gmail.com', 587, false);
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
        $email  = (new \Symfony\Component\Mime\Email())
            ->from(env('MAIL_FROM_ADDRESS'))
            ->to($para)
            ->subject($asunto)
            ->text($mensaje);
        $mailer->send($email);
    }

    /**
     * ADMIN - Simular el resultado de un evento
     * Procesa todas las apuestas pendientes del evento
     * Notifica a cada usuario si gano o perdio
     * Usa transaccion para procesar todo atomicamente
     * Endpoint: POST /api/eventos/{id}/resultado
     */
    public function simularResultado(Request $request, $id)
    {
        $request->validate([
            'resultado' => 'required|in:local,empate,visitante',
        ]);

        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }

        if ($evento->estado === 'finalizado') {
            return response()->json(['message' => 'Este evento ya fue finalizado'], 400);
        }

        DB::transaction(function () use ($evento, $request) {

            Resultado::create([
                'evento_id' => $evento->id,
                'resultado' => $request->resultado,
            ]);

            $evento->estado = 'finalizado';
            $evento->save();

            $apuestas = Apuesta::where('evento_id', $evento->id)
                                ->where('estado', 'pendiente')
                                ->with('usuario')
                                ->get();

            foreach ($apuestas as $apuesta) {
                if ($apuesta->tipo_apuesta === $request->resultado) {

                    $apuesta->estado = 'ganada';
                    $apuesta->save();

                    // Si falla el correo, el resultado igual se procesa
                    try {
                        $this->enviarCorreo(
                            $apuesta->usuario->email,
                            'Ganaste tu apuesta! - Apuestas Deportivas',
                            "Felicitaciones! Tu apuesta ha ganado!\n\nEvento: {$evento->equipo_local} vs {$evento->equipo_visitante}\nTipo apostado: {$apuesta->tipo_apuesta}\nGanancia: {$apuesta->ganancia}\n\nPuedes cobrar tu ganancia desde la app."
                        );
                    } catch (\Exception $e) {
                        // El correo fallo pero el resultado fue procesado
                    }

                } else {

                    $apuesta->estado = 'perdida';
                    $apuesta->save();

                    // Si falla el correo, el resultado igual se procesa
                    try {
                        $this->enviarCorreo(
                            $apuesta->usuario->email,
                            'Resultado de tu apuesta - Apuestas Deportivas',
                            "Tu apuesta ha perdido.\n\nEvento: {$evento->equipo_local} vs {$evento->equipo_visitante}\nTipo apostado: {$apuesta->tipo_apuesta}\nMonto apostado: {$apuesta->monto}\n\nSuerte en la proxima!"
                        );
                    } catch (\Exception $e) {
                        // El correo fallo pero el resultado fue procesado
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
     * ADMIN - Ajustar el saldo de un usuario
     * Endpoint: PUT /api/admin/usuarios/{id}/saldo
     */
    public function ajustarSaldo(Request $request, $id)
    {
        $request->validate([
            'saldo' => 'required|numeric|min:0',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $saldo_anterior = $user->saldo;
        $user->saldo    = $request->saldo;
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
     * Endpoint: GET /api/admin/usuarios
     */
    public function listarUsuarios()
    {
        $usuarios = User::where('rol', 'usuario')->get();
        return response()->json($usuarios);
    }
}
