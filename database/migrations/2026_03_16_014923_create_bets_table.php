<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta migracion crea la tabla "bets"
// Aqui se guardan las apuestas que realizan los usuarios en los eventos deportivos.

return new class extends Migration
{
    /**
     * Ejecuta la migracion
     */
    public function up(): void
    {
        Schema::create('bets', function (Blueprint $table) {

            // ID principal de la apuesta
            $table->id();

            // Usuario que realiza la apuesta
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Evento sobre el cual se hace la apuesta
            $table->foreignId('event_id')
                  ->constrained('events')
                  ->cascadeOnDelete();

            // Tipo de apuesta realizada
            // LOCAL -> gana el equipo local
            // EMPATE -> empate
            // VISITANTE -> gana el equipo visitante
            $table->enum('tipo_apuesta', [
                'LOCAL',
                'EMPATE',
                'VISITANTE'
            ]);

            // Cuota usada al momento de realizar la apuesta
            $table->decimal('cuota', 8, 2);

            // Monto apostado por el usuario
            $table->decimal('monto', 10, 2);

            // Moneda utilizada en la apuesta
            // COP -> pesos colombianos
            // USD -> dolares
            $table->enum('moneda', [
                'COP',
                'USD'
            ])->default('COP');

            // Ganancia potencial calculada segun cuota y monto
            $table->decimal('ganancia_potencial', 10, 2);

            // Estado de la apuesta
            // PENDIENTE -> esperando resultado del evento
            // GANADA -> el usuario gano la apuesta
            // PERDIDA -> el usuario perdio la apuesta
            $table->enum('estado', [
                'PENDIENTE',
                'GANADA',
                'PERDIDA'
            ])->default('PENDIENTE');

            // Fechas automaticas de Laravel
            // created_at -> cuando se creo la apuesta
            // updated_at -> ultima modificacion
            $table->timestamps();
        });
    }

    /**
     * Revierte la migracion
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};

