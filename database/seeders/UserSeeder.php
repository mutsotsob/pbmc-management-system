<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'takudzwa.mugwaku@acrnhealth.com'],
            [
                'name' => 'Takudzwa Mugwaku',
                'department' => 'Laboratory',
                'job_title' => 'Laboratory Manager',
                'phone_number' => '0777628465',
                'user_type' => 'admin',
                'user_status' => true, // ACTIVE
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]
        );

        // Regular user
        User::updateOrCreate(
            ['email' => 'blessward.mutsotso@acrnhealth.com'],
            [
                'name' => 'Blessward Mutsotso',
                'department' => 'IT and Data Systems',
                'job_title' => 'Software Developer Associate',
                'phone_number' => '0787780405',
                'user_type' => 'user',
                'user_status' => true, // ACTIVE
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]
        );
    }
}
