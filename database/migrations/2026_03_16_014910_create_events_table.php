<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta migracion crea la tabla "events".
// En esta tabla se guardaran los eventos deportivos
// sobre los cuales los usuarios podran hacer apuestas.

return new class extends Migration
{
    /**
     * Ejecuta la migracion.
     * Aqui definimos la estructura de la tabla events.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {

            // ID principal del evento.
            // Es la clave primaria autoincremental.
            $table->id();

            // Deporte del evento.
            // Ejemplo: Football, Basketball, Tennis
            $table->string('sport');

            // Equipo local
            // Ejemplo: Barcelona
            $table->string('home_team');

            // Equipo visitante
            // Ejemplo: Real Madrid
            $table->string('away_team');

            // Fecha y hora del evento deportivo
            $table->dateTime('event_date');

            // Estado del evento
            // OPEN -> evento abierto para apuestas
            // CLOSED -> apuestas cerradas
            // FINISHED -> evento terminado
            $table->enum('status', ['OPEN', 'CLOSED', 'FINISHED'])
                  ->default('OPEN');

            // Usuario que creo el evento
            // Se relaciona con la tabla users
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Campos automaticos de Laravel
            // created_at -> fecha de creacion
            // updated_at -> fecha de actualizacion
            $table->timestamps();
        });
    }

    /**
     * Revierte la migracion
     * Elimina la tabla events si se hace rollback
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
