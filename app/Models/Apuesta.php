<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Apuesta
 * Representa una apuesta realizada por un usuario en un evento deportivo
 *
 * Formula de ganancia:
 * ganancia = monto * cuota
 *
 * Estados posibles:
 * - pendiente
 * - ganada
 * - perdida
 * - cobrada
 *
 * Relaciones:
 * - pertenece a un usuario
 * - pertenece a un evento
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 01:25 COT
 * @version  1.0
 */
class Apuesta extends Model
{
    /**
     * Campos que se pueden llenar masivamente
     */
    protected $fillable = [
        'usuario_id',
        'evento_id',
        'tipo_apuesta',
        'monto',
        'cuota',
        'estado',
        'ganancia',
    ];

    /**
     * Formato personalizado para fechas del modelo
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d h:i a');
    }

    /**
     * Relacion: una apuesta pertenece a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relacion: una apuesta pertenece a un evento
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}

