<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta migracion crea la tabla "odds"
// Aqui se guardan las cuotas disponibles para apostar en un evento deportivo.

return new class extends Migration
{
    /**
     * Ejecuta la migracion
     */
    public function up(): void
    {
        Schema::create('odds', function (Blueprint $table) {

            // ID principal de la cuota
            $table->id();

            // Evento al que pertenece la cuota
            $table->foreignId('event_id')
                  ->constrained('events')
                  ->cascadeOnDelete();

            // Tipo de apuesta
            // HOME_WIN -> gana el equipo local
            // DRAW -> empate
            // AWAY_WIN -> gana el equipo visitante
            $table->enum('bet_type', [
                'HOME_WIN',
                'DRAW',
                'AWAY_WIN'
            ]);

            // Valor de la cuota
            // Ejemplo: 1.8 , 2.3 , 3.5
            $table->decimal('odd', 8, 2);

            // Fechas automaticas
            $table->timestamps();
        });
    }

    /**
     * Revierte la migracion
     */
    public function down(): void
    {
        Schema::dropIfExists('odds');
    }
};
