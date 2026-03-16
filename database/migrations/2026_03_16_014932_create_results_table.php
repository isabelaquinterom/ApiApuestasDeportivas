<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta migracion crea la tabla results
// Aqui se guarda el resultado final de cada evento deportivo

return new class extends Migration
{
    /**
     * Ejecuta la migracion
     */
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {

            // ID principal del resultado
            $table->id();

            // Evento al que pertenece el resultado
            // Un evento solo debe tener un resultado final
            $table->foreignId('event_id')
                  ->unique()
                  ->constrained('events')
                  ->cascadeOnDelete();

            // Resultado del evento
            // LOCAL -> gana equipo local
            // EMPATE -> empate
            // VISITANTE -> gana equipo visitante
            $table->enum('resultado', [
                'LOCAL',
                'EMPATE',
                'VISITANTE'
            ]);

            // Fechas automaticas de Laravel
            $table->timestamps();
        });
    }

    /**
     * Revierte la migracion
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
