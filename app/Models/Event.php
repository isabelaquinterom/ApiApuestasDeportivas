<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    /**
     * Campos que se pueden guardar masivamente
     */
    protected $fillable = [
        'deporte',
        'equipo_local',
        'equipo_visitante',
        'fecha_evento',
        'estado',
        'created_by',
    ];

    /**
     * Un evento puede tener muchas cuotas
     */
    public function odds()
    {
        return $this->hasMany(Odd::class);
    }

    /**
     * Un evento puede tener muchas apuestas
     */
    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    /**
     * Un evento tiene un resultado final
     */
    public function result()
    {
        return $this->hasOne(Result::class);
    }

    /**
     * Formatear fecha del evento
     */
    public function getFechaEventoAttribute($value)
    {
        return Carbon::parse($value)
            ->timezone('America/Bogota')
            ->format('d-m-Y H:i');
    }

    /**
     * Formatear fecha de creacion
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)
            ->timezone('America/Bogota')
            ->format('d-m-Y H:i');
    }

    /**
     * Formatear fecha de actualizacion
     */
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)
            ->timezone('America/Bogota')
            ->format('d-m-Y H:i');
    }
}
