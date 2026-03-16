<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'nombre'    => 'Admin',
            'email'     => 'admin@apuestas.com',
            'password'  => Hash::make('admin123'),
            'rol'       => 'admin',
            'saldo'     => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
