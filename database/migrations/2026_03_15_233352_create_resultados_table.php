<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('resultados', function (Blueprint $table) {
            $table->id();                                                               // ID unico del resultado
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade'); // Evento relacionado
            $table->enum('resultado', ['local', 'empate', 'visitante']);                // Resultado final
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados');
    }
};

