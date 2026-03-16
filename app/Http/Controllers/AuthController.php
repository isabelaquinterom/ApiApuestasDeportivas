<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

/**
 * Controlador de Autenticacion
 * Maneja el registro, login con 2FA, verificacion OTP, logout y perfil del usuario
 *
 * Flujo de login:
 * 1. El usuario envia email y password
 * 2. Se validan las credenciales
 * 3. Se genera un codigo OTP
 * 4. El OTP se envia al correo
 * 5. El usuario valida el OTP
 * 6. Se entrega el token JWT
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 02:00 COT
 * @version  1.0
 */
class AuthController extends Controller
{
    /**
     * REGISTRO
     * Crea un nuevo usuario en la base de datos
     * Endpoint: POST /api/register
     */
    public function register(Request $request)
    {
        // Validar que los campos requeridos esten presentes y sean correctos
        $request->validate([
            'nombre'   => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        // Crear el usuario con la contrasena encriptada usando bcrypt
        $user = User::create([
            'nombre'   => $request->nombre,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'saldo'    => 0,
            'rol'      => 'usuario',
        ]);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => $user
        ], 201);
    }

    /**
     * LOGIN - PASO 1
     * Valida credenciales y envia codigo OTP al correo del usuario
     * Endpoint: POST /api/login
     */
    public function login(Request $request)
    {
        // Validar campos requeridos
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Buscar usuario por email
        $user = User::where('email', $request->email)->first();

        // Verificar que el usuario exista y la contrasena sea correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Generar codigo OTP aleatorio de 6 digitos
        $otp = rand(100000, 999999);

        // Guardar OTP en la base de datos con expiracion de 5 minutos
        $user->otp_code       = $otp;
        $user->otp_expiration = Carbon::now()->addMinutes(5);
        $user->save();

        // Enviar OTP por correo usando Symfony Mailer directamente
        // Se usa este metodo para evitar problemas de SSL en Windows
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
            ->to($user->email)
            ->subject('Codigo de verificacion - Apuestas Deportivas')
            ->text("Tu codigo de verificacion es: $otp \nEste codigo expira en 5 minutos.");

        $mailer->send($email);

        return response()->json([
            'message' => 'Codigo OTP enviado a tu correo. Expira en 5 minutos.'
        ]);
    }

    /**
     * LOGIN - PASO 2
     * Verifica el OTP recibido por correo y entrega el token JWT
     * Endpoint: POST /api/verify-otp
     */
    public function verifyOtp(Request $request)
    {
        // Validar campos requeridos
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required',
        ]);

        // Buscar usuario por email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Verificar que el OTP ingresado coincida con el guardado
        if ($user->otp_code != $request->otp) {
            return response()->json([
                'message' => 'Codigo OTP incorrecto'
            ], 401);
        }

        // Verificar que exista fecha de expiracion y que no haya expirado
        if (!$user->otp_expiration || Carbon::now()->isAfter($user->otp_expiration)) {
            return response()->json([
                'message' => 'El codigo OTP ha expirado'
            ], 401);
        }

        // Limpiar el OTP de la base de datos una vez usado exitosamente
        $user->otp_code       = null;
        $user->otp_expiration = null;
        $user->save();

        // Generar token JWT para el usuario autenticado
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login exitoso',
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'nombre' => $user->nombre,
                'email'  => $user->email,
                'rol'    => $user->rol,
                'saldo'  => $user->saldo,
            ]
        ]);
    }

    /**
     * LOGOUT
     * Invalida el token JWT actual del usuario
     * Endpoint: POST /api/logout
     */
    public function logout()
    {
        // Invalidar el token JWT para que no pueda ser usado de nuevo
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => 'Sesion cerrada correctamente'
        ]);
    }

    /**
     * ME
     * Retorna la informacion del usuario autenticado
     * Endpoint: GET /api/me
     */
    public function me()
    {
        // Obtener el usuario autenticado desde el token
        $user = JWTAuth::parseToken()->authenticate();

        // Retornar solo la informacion importante del perfil
        return response()->json([
            'id'     => $user->id,
            'nombre' => $user->nombre,
            'email'  => $user->email,
            'rol'    => $user->rol,
            'saldo'  => $user->saldo,
        ]);
    }
}
