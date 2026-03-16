<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Cuota
 * Representa el valor de una apuesta para un tipo especifico en un evento
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 01:25 COT
 * @version  1.0
 */
class Cuota extends Model
{
    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'evento_id',
        'tipo_apuesta',
        'cuota',
    ];

    // Formato de fecha: 2026-03-16 01:25 am
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d h:i a');
    }

    /**
     * Relacion: una cuota pertenece a un evento
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}


