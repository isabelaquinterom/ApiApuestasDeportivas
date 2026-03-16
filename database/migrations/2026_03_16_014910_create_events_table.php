<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta migracion crea la tabla events
// Aqui se guardan los eventos deportivos sobre los cuales los usuarios pueden apostar

return new class extends Migration
{
    /**
     * Ejecuta la migracion
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {

            // ID principal del evento
            $table->id();

            // Deporte del evento
            // Ejemplo: Futbol, Baloncesto, Tenis
            $table->string('deporte');

            // Equipo local
            $table->string('equipo_local');

            // Equipo visitante
            $table->string('equipo_visitante');

            // Fecha y hora del evento deportivo
            $table->dateTime('fecha_evento');

            // Estado del evento
            // ABIERTO -> acepta apuestas
            // CERRADO -> ya no acepta apuestas
            // FINALIZADO -> evento terminado
            $table->enum('estado', [
                'ABIERTO',
                'CERRADO',
                'FINALIZADO'
            ])->default('ABIERTO');

            // Usuario que creo el evento
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Fechas automaticas de Laravel
            $table->timestamps();
        });
    }

    /**
     * Revierte la migracion
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

