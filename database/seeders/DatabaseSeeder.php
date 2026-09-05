<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@artship.test',
            'email_lookup' => app(\App\Services\LookupService::class)->emailLookup('admin@artship.test'),
            'password' => 'ChangeMe123!',
            'role' => 'admin',
        ]);
    }
}
