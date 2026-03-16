<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Resultado
 * Representa el resultado final de un evento deportivo
 * Es registrado por el administrador para procesar las apuestas
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-16 01:25 COT
 * @version  1.0
 */
class Resultado extends Model
{
    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'evento_id',
        'resultado',
    ];

    // Formato de fecha: 2026-03-16 01:25 am
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d h:i a');
    }

    /**
     * Relacion: un resultado pertenece a un evento
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}