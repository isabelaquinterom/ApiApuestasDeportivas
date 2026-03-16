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
        $eventos = Event::all();

        return response()->json($eventos);
    }

    /**
     * Crear un nuevo evento
     */
    public function store(Request $request)
    {
        $request->validate([
            'deporte' => 'required|string|max:255',
            'equipo_local' => 'required|string|max:255',
            'equipo_visitante' => 'required|string|max:255',
            'fecha_evento' => 'required|date',
        ]);

        $evento = Event::create([
            'deporte' => $request->deporte,
            'equipo_local' => $request->equipo_local,
            'equipo_visitante' => $request->equipo_visitante,
            'fecha_evento' => $request->fecha_evento,
            'estado' => 'ABIERTO',
            'created_by' => 1
        ]);

        return response()->json([
            'mensaje' => 'Evento creado correctamente',
            'data' => $evento
        ], 201);
    }

    /**
     * Ver un evento especifico
     */
    public function show($id)
    {
        $evento = Event::findOrFail($id);

        return response()->json($evento);
    }
}

