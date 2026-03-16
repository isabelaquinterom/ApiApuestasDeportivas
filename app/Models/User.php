<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * Modelo User
 * Representa a los usuarios del sistema (admin y usuario)
 * Implementa JWTSubject para poder generar tokens JWT
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 01:25 COT
 * @version  1.0
 */
class User extends Authenticatable implements JWTSubject
{
    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'saldo',
        'rol',
        'otp_code',
        'otp_expiration',
    ];

    // Campos que NO se muestran en las respuestas JSON por seguridad
    protected $hidden = [
        'password',
        'otp_code',
        'otp_expiration',
    ];

    // Formato de fecha: 2026-03-16 01:25 am
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d h:i a');
    }

    /**
     * Metodo requerido por JWT
     * Retorna el identificador unico del usuario para el token
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Metodo requerido por JWT
     * Retorna claims adicionales para el token (vacio en este caso)
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relacion: un usuario tiene muchas apuestas
     */
    public function apuestas()
    {
        return $this->hasMany(Apuesta::class, 'usuario_id');
    }
}

