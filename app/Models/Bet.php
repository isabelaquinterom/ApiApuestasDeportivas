<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    /**
     * La apuesta pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * La apuesta pertenece a un evento
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Una apuesta puede tener movimientos de dinero
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

