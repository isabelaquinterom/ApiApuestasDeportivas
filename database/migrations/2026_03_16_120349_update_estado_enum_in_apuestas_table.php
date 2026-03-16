<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE apuestas 
            MODIFY estado ENUM('pendiente', 'ganada', 'perdida', 'cobrada')
            DEFAULT 'pendiente'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE apuestas 
            MODIFY estado ENUM('pendiente', 'ganada', 'perdida')
            DEFAULT 'pendiente'
        ");
    }
};

