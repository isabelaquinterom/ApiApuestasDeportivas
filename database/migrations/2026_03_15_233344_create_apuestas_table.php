<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('apuestas', function (Blueprint $table) {
            $table->id();                                                                // ID unico de la apuesta
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade'); // Usuario que apuesta
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade'); // Evento apostado
            $table->enum('tipo_apuesta', ['local', 'empate', 'visitante']);             // Tipo de apuesta
            $table->decimal('monto', 10, 2);                                            // Monto apostado
            $table->decimal('cuota', 5, 2);                                             // Cuota al momento de apostar
            $table->enum('estado', ['pendiente', 'ganada', 'perdida'])->default('pendiente'); // Estado de la apuesta
            $table->decimal('ganancia', 10, 2)->nullable();                             // Ganancia si gano
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apuestas');
    }
};

