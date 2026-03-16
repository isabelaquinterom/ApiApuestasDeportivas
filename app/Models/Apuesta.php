<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Apuesta
 * Representa una apuesta realizada por un usuario en un evento deportivo
 * Calculo de ganancia: monto * cuota
 *
 * @isabela  Proyecto Apuestas Deportivas
 * @date     2026-03-15 23:44 COT
 * @version  1.0
 */
class Apuesta extends Model
{
    // Campos que se pueden llenar masivamente
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

