<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
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
}
