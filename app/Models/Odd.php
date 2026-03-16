<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odd extends Model
{
    /**
     * Cada cuota pertenece a un evento
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

