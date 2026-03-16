<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                                       // ID unico del usuario
            $table->string('nombre');                                           // Nombre del usuario
            $table->string('email')->unique();                                  // Correo unico
            $table->string('password');                                         // Contrasena encriptada
            $table->decimal('saldo', 10, 2)->default(0);                       // Saldo disponible
            $table->enum('rol', ['admin', 'usuario'])->default('usuario');     // Rol del usuario
            $table->string('otp_code')->nullable();                            // Codigo OTP para 2FA
            $table->timestamp('otp_expiration')->nullable();                   // Expiracion del OTP
            $table->timestamps();                                               // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
