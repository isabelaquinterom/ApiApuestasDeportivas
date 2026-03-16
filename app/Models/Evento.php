<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Evento
 * Representa un evento deportivo sobre el cual se pueden realizar apuestas
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 01:25 COT
 * @version  1.0
 */
class Evento extends Model
{
    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'deporte',
        'equipo_local',
        'equipo_visitante',
        'fecha',
        'estado',
    ];

    // Formato de fecha: 2026-03-16 01:25 am
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d h:i a');
    }

    /**
     * Relacion: un evento tiene muchas cuotas (local, empate, visitante)
     */
    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }

    /**
     * Relacion: un evento tiene muchas apuestas de usuarios
     */
    public function apuestas()
    {
        return $this->hasMany(Apuesta::class);
    }

    /**
     * Relacion: un evento tiene un resultado final
     */
    public function resultado()
    {
        return $this->hasOne(Resultado::class);
    }
}
