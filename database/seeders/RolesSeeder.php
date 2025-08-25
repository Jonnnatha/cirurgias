<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cria os papéis
        $admin     = Role::firstOrCreate(['name' => 'admin']);
        $enfermeiro = Role::firstOrCreate(['name' => 'enfermeiro']);
        $medico     = Role::firstOrCreate(['name' => 'medico']);

        // Cria um usuário admin padrão
        $user = User::firstOrCreate(
            ['email' => 'jonnnatha'],
            ['name' => 'Admin', 'password' => bcrypt('123')]
        );

        $user->assignRole($admin);
    }
}
