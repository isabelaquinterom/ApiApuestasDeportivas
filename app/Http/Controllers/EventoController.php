<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Cuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Eventos Deportivos
 * Maneja la creacion y consulta de eventos deportivos y sus cuotas
 *
 * Acciones del Admin:   crear eventos con cuotas
 * Acciones del Usuario: ver eventos disponibles
 *
 * @author   Proyecto Apuestas Deportivas
 * @date     2026-03-15 23:44 COT
 * @version  1.0
 */
class EventoController extends Controller
{
    /**
     * ADMIN - Crear un nuevo evento deportivo con sus cuotas
     * Usa transaccion para garantizar que el evento y las cuotas
     * se creen juntos o no se cree ninguno
     * Endpoint: POST /api/eventos
     */
    public function crear(Request $request)
    {
        $request->validate([
            'deporte'          => 'required|string',
            'equipo_local'     => 'required|string',
            'equipo_visitante' => 'required|string',
            'fecha'            => 'required|date',
            'cuota_local'      => 'required|numeric|min:1',
            'cuota_empate'     => 'required|numeric|min:1',
            'cuota_visitante'  => 'required|numeric|min:1',
        ]);

        // Usar transaccion: si algo falla, se revierte todo
        $evento = DB::transaction(function () use ($request) {

            // Paso 1: Crear el evento deportivo
            $evento = Evento::create([
                'deporte'          => $request->deporte,
                'equipo_local'     => $request->equipo_local,
                'equipo_visitante' => $request->equipo_visitante,
                'fecha'            => $request->fecha,
                'estado'           => 'pendiente',
            ]);

            // Paso 2: Crear las 3 cuotas del evento (local, empate, visitante)
            Cuota::create(['evento_id' => $evento->id, 'tipo_apuesta' => 'local',     'cuota' => $request->cuota_local]);
            Cuota::create(['evento_id' => $evento->id, 'tipo_apuesta' => 'empate',    'cuota' => $request->cuota_empate]);
            Cuota::create(['evento_id' => $evento->id, 'tipo_apuesta' => 'visitante', 'cuota' => $request->cuota_visitante]);

            return $evento;
        });

        return response()->json([
            'message' => 'Evento creado correctamente',
            'evento'  => $evento->load('cuotas')
        ], 201);
    }

    /**
     * USUARIO y ADMIN - Ver todos los eventos disponibles
     * Endpoint: GET /api/eventos
     */
    public function listar()
    {
        $eventos = Evento::with('cuotas')->get();
        return response()->json($eventos);
    }

    /**
     * USUARIO y ADMIN - Ver un evento especifico por ID
     * Endpoint: GET /api/eventos/{id}
     */
    public function ver($id)
    {
        $evento = Evento::with(['cuotas', 'resultado'])->find($id);

        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }

        return response()->json($evento);
    }
}

