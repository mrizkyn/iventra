<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Role "Owner" dan simpan hasilnya ke dalam variabel
        $ownerRole = Role::firstOrCreate(
            ['role_name' => 'Owner'],
            ['description' => 'Has all access to the system.']
        );

        // 2. Buat Role "Admin"
        Role::firstOrCreate(
            ['role_name' => 'Admin'],
            ['description' => 'Manages day-to-day operations.']
        );

        // 3. Buat User Owner menggunakan ID dari variabel $ownerRole
        User::firstOrCreate(
            ['email' => 'owner@laluna.id'],
            [
                'name' => 'Owner',
                'password' => Hash::make('123123'),
                'role_id' => $ownerRole->id, // <-- PERBAIKAN DI SINI
            ]
        );
    }
}
