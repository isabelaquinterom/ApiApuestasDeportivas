<?php

namespace App\Http\Controllers;

use App\Models\Apuesta;
use App\Models\Evento;
use App\Models\Cuota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Controlador de Apuestas
 * Maneja la logica de apuestas, consulta y cobro de ganancias
 *
 * Acciones del Usuario: apostar, ver sus apuestas, cobrar ganancias
 * Acciones del Admin:   ver todas las apuestas
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 09:30 COT
 * @version  1.0
 */
class ApuestaController extends Controller
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
     * USUARIO - Realizar una apuesta en un evento
     * Usa transaccion para descontar saldo y registrar apuesta juntos
     * Formula de ganancia: monto * cuota
     * Endpoint: POST /api/apuestas
     */
    public function apostar(Request $request)
    {
        // Validar campos requeridos
        $request->validate([
            'evento_id'    => 'required|exists:eventos,id',
            'tipo_apuesta' => 'required|in:local,empate,visitante',
            'monto'        => 'required|numeric|min:1',
        ]);

        // Obtener el usuario autenticado desde el token JWT
        $user = JWTAuth::parseToken()->authenticate();

        // Verificar que el evento exista y este pendiente
        $evento = Evento::find($request->evento_id);
        if ($evento->estado !== 'pendiente') {
            return response()->json(['message' => 'Este evento ya finalizo'], 400);
        }

        // Verificar que el usuario tenga suficiente saldo
        if ($user->saldo < $request->monto) {
            return response()->json(['message' => 'Saldo insuficiente'], 400);
        }

        // Buscar la cuota para este tipo de apuesta
        $cuota = Cuota::where('evento_id', $request->evento_id)
                       ->where('tipo_apuesta', $request->tipo_apuesta)
                       ->first();

        if (!$cuota) {
            return response()->json(['message' => 'Cuota no encontrada'], 404);
        }

        // Calcular ganancia potencial: monto * cuota
        $ganancia_potencial = $request->monto * $cuota->cuota;

        // Usar transaccion para garantizar consistencia en los datos
        // Si algo falla, el saldo y la apuesta se revierten juntos
        $apuesta = DB::transaction(function () use ($user, $request, $cuota, $ganancia_potencial) {

            // Descontar el monto apostado del saldo del usuario
            $user->saldo -= $request->monto;
            $user->save();

            // Registrar la apuesta en la base de datos
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

        // Enviar correo FUERA de la transaccion
        // Si falla el correo, la apuesta igual queda registrada
        try {
            $this->enviarCorreo(
                $user->email,
                'Confirmacion de apuesta - Apuestas Deportivas',
                "Tu apuesta ha sido registrada.\n\nEvento: {$evento->equipo_local} vs {$evento->equipo_visitante}\nTipo: {$request->tipo_apuesta}\nMonto apostado: {$request->monto}\nGanancia potencial: {$ganancia_potencial}\n\nBuena suerte!"
            );
        } catch (\Exception $e) {
            // El correo fallo pero la apuesta fue registrada correctamente
        }

        return response()->json([
            'message'      => 'Apuesta realizada correctamente',
            'apuesta'      => $apuesta,
            'saldo_actual' => $user->saldo
        ], 201);
    }

    /**
     * USUARIO - Ver sus propias apuestas
     * Retorna todas las apuestas del usuario autenticado
     * Endpoint: GET /api/apuestas/mis
     */
    public function misApuestas()
    {
        // Obtener usuario autenticado
        $user = JWTAuth::parseToken()->authenticate();

        // Traer las apuestas del usuario con informacion del evento
        $apuestas = Apuesta::where('usuario_id', $user->id)
                            ->with('evento')
                            ->get();

        return response()->json($apuestas);
    }

    /**
     * USUARIO - Cobrar una apuesta ganada
     * Acredita la ganancia al saldo del usuario
     * Usa transaccion para acreditar saldo y marcar apuesta juntos
     * Endpoint: POST /api/apuestas/{id}/cobrar
     */
    public function cobrar($id)
    {
        // Obtener usuario autenticado
        $user = JWTAuth::parseToken()->authenticate();

        // Buscar la apuesta que pertenezca al usuario
        $apuesta = Apuesta::where('id', $id)
                           ->where('usuario_id', $user->id)
                           ->first();

        if (!$apuesta) {
            return response()->json(['message' => 'Apuesta no encontrada'], 404);
        }

        // Verificar que la apuesta este en estado ganada
        if ($apuesta->estado !== 'ganada') {
            return response()->json(['message' => 'Esta apuesta no esta ganada o ya fue cobrada'], 400);
        }

        // Usar transaccion para acreditar saldo y marcar como cobrada juntos
        DB::transaction(function () use ($user, $apuesta) {

            // Acreditar la ganancia al saldo del usuario
            $user->saldo += $apuesta->ganancia;
            $user->save();

            // Marcar la apuesta como cobrada
            $apuesta->estado = 'cobrada';
            $apuesta->save();
        });

        // Enviar correo FUERA de la transaccion
        // Si falla el correo, el cobro igual se completa
        try {
            $this->enviarCorreo(
                $user->email,
                'Cobro de ganancia - Apuestas Deportivas',
                "Has cobrado tu apuesta ganada.\n\nGanancia acreditada: {$apuesta->ganancia}\nSaldo actual: {$user->saldo}\n\nGracias por usar Apuestas Deportivas!"
            );
        } catch (\Exception $e) {
            // El correo fallo pero el cobro fue exitoso
        }

        return response()->json([
            'message'      => 'Ganancia cobrada correctamente',
            'ganancia'     => $apuesta->ganancia,
            'saldo_actual' => $user->saldo
        ]);
    }

    /**
     * ADMIN - Ver todas las apuestas de todos los usuarios
     * Endpoint: GET /api/admin/apuestas
     */
    public function todasLasApuestas()
    {
        // Traer todas las apuestas con informacion del usuario y evento
        $apuestas = Apuesta::with(['usuario', 'evento'])->get();
        return response()->json($apuestas);
    }
}
