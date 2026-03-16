<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();                                                      // ID unico del evento
            $table->string('deporte');                                         // Tipo de deporte
            $table->string('equipo_local');                                    // Equipo local
            $table->string('equipo_visitante');                                // Equipo visitante
            $table->dateTime('fecha');                                         // Fecha del evento
            $table->enum('estado', ['pendiente', 'finalizado'])->default('pendiente'); // Estado del evento
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};

