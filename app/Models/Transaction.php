<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * La transaccion pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * La transaccion puede estar relacionada a una apuesta
     */
    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }
}

