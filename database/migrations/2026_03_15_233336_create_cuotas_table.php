<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();                                                               // ID unico de la cuota
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade'); // Relacion con evento
            $table->enum('tipo_apuesta', ['local', 'empate', 'visitante']);             // Tipo de apuesta
            $table->decimal('cuota', 5, 2);                                            // Valor de la cuota
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};

