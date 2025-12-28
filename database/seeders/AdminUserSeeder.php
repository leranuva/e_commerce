<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe un usuario admin
        if (User::where('email', 'admin@ecommerce.com')->exists()) {
            $this->command->info('Usuario admin ya existe. Saltando creación...');
            return;
        }

        // Crear usuario admin
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@ecommerce.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('Usuario admin creado exitosamente!');
        $this->command->info('Email: admin@ecommerce.com');
        $this->command->info('Password: password');
        $this->command->warn('⚠️  IMPORTANTE: Cambia la contraseña después del primer inicio de sesión!');
    }
}
