<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Evento
 * Representa un evento deportivo sobre el cual se pueden realizar apuestas
 *
 * Campos principales:
 * - deporte
 * - equipo_local
 * - equipo_visitante
 * - fecha
 * - estado
 *
 * Relaciones:
 * - un evento tiene muchas cuotas
 * - un evento tiene muchas apuestas
 * - un evento tiene un resultado
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 01:25 COT
 * @version  1.0
 */
class Evento extends Model
{
    /**
     * Campos que se pueden llenar masivamente
     */
    protected $fillable = [
        'deporte',
        'equipo_local',
        'equipo_visitante',
        'fecha',
        'estado',
    ];

    /**
     * Formato personalizado para las fechas del modelo
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d h:i a');
    }

    /**
     * Relacion: un evento tiene muchas cuotas
     * Ejemplo:
     * - local
     * - empate
     * - visitante
     */
    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }

    /**
     * Relacion: un evento tiene muchas apuestas
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

