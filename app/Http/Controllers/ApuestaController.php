<?php

namespace App\Http\Controllers;

use App\Models\Apuesta;
use App\Models\Evento;
use App\Models\Cuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Controlador de Apuestas
 * Maneja la logica de apuestas, consulta y cobro de ganancias
 *
 * Funciones del usuario:
 * - Realizar apuestas
 * - Ver sus apuestas
 * - Cobrar apuestas ganadas
 *
 * Funciones del admin:
 * - Ver todas las apuestas del sistema
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 09:30 COT
 * @version  1.0
 */
class ApuestaController extends Controller
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
     * USUARIO
     * Realizar una apuesta en un evento
     *
     * Endpoint: POST /api/apuestas
     *
     * Flujo:
     * 1. Validar datos recibidos
     * 2. Obtener usuario autenticado
     * 3. Verificar que el evento siga pendiente
     * 4. Validar saldo disponible
     * 5. Buscar la cuota segun el tipo de apuesta
     * 6. Calcular ganancia potencial
     * 7. Descontar saldo y registrar apuesta en transaccion
     * 8. Enviar correo de confirmacion
     */
    public function apostar(Request $request)
    {
        // Validar campos requeridos
        $request->validate([
            'evento_id'    => 'required|exists:eventos,id',
            'tipo_apuesta' => 'required|in:local,empate,visitante',
            'monto'        => 'required|numeric|min:1',
        ]);

        // Obtener usuario autenticado desde el token JWT
        $user = JWTAuth::parseToken()->authenticate();

        // Buscar evento
        $evento = Evento::find($request->evento_id);

        // Verificar que el evento siga disponible para apuestas
        if ($evento->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Este evento ya finalizo'
            ], 400);
        }

        // Validar saldo suficiente
        if ($user->saldo < $request->monto) {
            return response()->json([
                'message' => 'Saldo insuficiente'
            ], 400);
        }

        // Buscar la cuota para el evento y el tipo de apuesta
        $cuota = Cuota::where('evento_id', $request->evento_id)
            ->where('tipo_apuesta', $request->tipo_apuesta)
            ->first();

        if (!$cuota) {
            return response()->json([
                'message' => 'Cuota no encontrada'
            ], 404);
        }

        // Calcular ganancia potencial
        $ganancia_potencial = $request->monto * $cuota->cuota;

        // Transaccion para descontar saldo y registrar la apuesta
        $apuesta = DB::transaction(function () use ($user, $request, $cuota, $ganancia_potencial) {

            /**
             * Paso 1
             * Descontar saldo al usuario
             */
            $user->saldo -= $request->monto;
            $user->save();

            /**
             * Paso 2
             * Crear la apuesta
             */
            $apuesta = Apuesta::create([
                'usuario_id'   => $user->id,
                'evento_id'    => $request->evento_id,
                'tipo_apuesta' => $request->tipo_apuesta,
                'monto'        => $request->monto,
                'cuota'        => $cuota->cuota,
                'estado'       => 'pendiente',
                'ganancia'     => $ganancia_potencial,
            ]);

            return $apuesta;
        });

        // Enviar correo fuera de la transaccion
        try {
            $this->enviarCorreo(
                $user->email,
                'Confirmacion de apuesta - Apuestas Deportivas',
                "Tu apuesta ha sido registrada.\n\n" .
                "Evento: {$evento->equipo_local} vs {$evento->equipo_visitante}\n" .
                "Tipo: {$request->tipo_apuesta}\n" .
                "Monto apostado: {$request->monto}\n" .
                "Ganancia potencial: {$ganancia_potencial}\n\n" .
                "Buena suerte!"
            );
        } catch (\Exception $e) {
            // Si falla el correo, la apuesta igual queda registrada
        }

        return response()->json([
            'message'      => 'Apuesta realizada correctamente',
            'apuesta'      => $apuesta,
            'saldo_actual' => $user->saldo
        ], 201);
    }

    /**
     * USUARIO
     * Ver sus propias apuestas
     *
     * Endpoint: GET /api/apuestas/mis
     */
    public function misApuestas()
    {
        // Obtener usuario autenticado
        $user = JWTAuth::parseToken()->authenticate();

        // Buscar apuestas del usuario con informacion del evento
        $apuestas = Apuesta::where('usuario_id', $user->id)
            ->with('evento')
            ->get();

        return response()->json([
            'message' => 'Listado de mis apuestas',
            'data'    => $apuestas
        ]);
    }

    /**
     * USUARIO
     * Cobrar una apuesta ganada
     *
     * Endpoint: POST /api/apuestas/{id}/cobrar
     *
     * Flujo:
     * 1. Obtener usuario autenticado
     * 2. Buscar la apuesta del usuario
     * 3. Verificar que este en estado ganada
     * 4. Acreditar saldo
     * 5. Marcar apuesta como cobrada
     * 6. Enviar correo de confirmacion
     */
    public function cobrar($id)
    {
        // Obtener usuario autenticado
        $user = JWTAuth::parseToken()->authenticate();

        // Buscar apuesta que pertenezca al usuario
        $apuesta = Apuesta::where('id', $id)
            ->where('usuario_id', $user->id)
            ->first();

        if (!$apuesta) {
            return response()->json([
                'message' => 'Apuesta no encontrada'
            ], 404);
        }

        // Verificar que la apuesta este en estado ganada
        if ($apuesta->estado !== 'ganada') {
            return response()->json([
                'message' => 'Esta apuesta no esta ganada o ya fue cobrada'
            ], 400);
        }

        // Transaccion para acreditar saldo y marcar apuesta como cobrada
        DB::transaction(function () use ($user, $apuesta) {

            /**
             * Paso 1
             * Sumar ganancia al saldo del usuario
             */
            $user->saldo += $apuesta->ganancia;
            $user->save();

            /**
             * Paso 2
             * Marcar la apuesta como cobrada
             */
            $apuesta->estado = 'cobrada';
            $apuesta->save();
        });

        // Enviar correo fuera de la transaccion
        try {
            $this->enviarCorreo(
                $user->email,
                'Cobro de ganancia - Apuestas Deportivas',
                "Has cobrado tu apuesta ganada.\n\n" .
                "Ganancia acreditada: {$apuesta->ganancia}\n" .
                "Saldo actual: {$user->saldo}\n\n" .
                "Gracias por usar Apuestas Deportivas!"
            );
        } catch (\Exception $e) {
            // Si falla el correo, el cobro igual se completa
        }

        return response()->json([
            'message'      => 'Ganancia cobrada correctamente',
            'ganancia'     => $apuesta->ganancia,
            'saldo_actual' => $user->saldo
        ]);
    }

    /**
     * ADMIN
     * Ver todas las apuestas del sistema
     *
     * Endpoint: GET /api/admin/apuestas
     */
    public function todasLasApuestas()
    {
        // Buscar todas las apuestas con usuario y evento
        $apuestas = Apuesta::with(['usuario', 'evento'])->get();

        return response()->json([
            'message' => 'Listado de todas las apuestas',
            'data'    => $apuestas
        ]);
    }
}

