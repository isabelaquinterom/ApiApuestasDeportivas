<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    /**
     * El resultado pertenece a un evento
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

