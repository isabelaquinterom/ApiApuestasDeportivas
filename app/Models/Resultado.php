<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Resultado
 * Representa el resultado final de un evento deportivo
 * Es registrado por el administrador para procesar las apuestas
 *
 * @aisabela  Proyecto Apuestas Deportivas
 * @date     2026-03-15 23:44 COT
 * @version  1.0
 */
class Resultado extends Model
{
    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'evento_id',
        'resultado',
    ];

    /**
     * Relacion: un resultado pertenece a un evento
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}
