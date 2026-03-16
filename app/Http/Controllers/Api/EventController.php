<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Listar todos los eventos
     */
    public function index()
    {
        $events = Event::all();

        return response()->json($events);
    }

    /**
     * Crear un nuevo evento
     */
    public function store(Request $request)
    {
        $event = Event::create([
            'sport' => $request->sport,
            'home_team' => $request->home_team,
            'away_team' => $request->away_team,
            'event_date' => $request->event_date,
            'status' => 'ABIERTO',
            'created_by' => 1
        ]);

        return response()->json($event);
    }

    /**
     * Ver un evento especifico
     */
    public function show($id)
    {
        $event = Event::findOrFail($id);

        return response()->json($event);
    }
}

