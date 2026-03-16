<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Cuota
 * Representa el valor de una apuesta para un tipo especifico en un evento
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-15 23:44 COT
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

    /**
     * Relacion: una cuota pertenece a un evento
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}

