<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta migracion crea la tabla transactions
// Aqui se registran todos los movimientos de dinero de los usuarios

return new class extends Migration
{
    /**
     * Ejecuta la migracion
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {

            // ID del movimiento
            $table->id();

            // Usuario al que pertenece la transaccion
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Apuesta relacionada (opcional)
            $table->foreignId('bet_id')
                  ->nullable()
                  ->constrained('bets')
                  ->cascadeOnDelete();

            // Tipo de movimiento
            // DEPOSITO -> recarga de dinero
            // APUESTA -> dinero usado en apuesta
            // GANANCIA -> dinero ganado
            // RETIRO -> retiro del usuario
            $table->enum('tipo', [
                'DEPOSITO',
                'APUESTA',
                'GANANCIA',
                'RETIRO'
            ]);

            // Monto del movimiento
            $table->decimal('monto', 10, 2);

            // Moneda utilizada
            // COP -> pesos colombianos
            // USD -> dolares
            $table->enum('moneda', [
                'COP',
                'USD'
            ])->default('COP');

            // Descripcion opcional del movimiento
            $table->string('descripcion')->nullable();

            // Fechas automaticas
            $table->timestamps();
        });
    }

    /**
     * Revierte la migracion
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

