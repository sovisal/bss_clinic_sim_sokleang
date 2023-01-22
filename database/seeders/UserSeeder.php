<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate([
            'id' => 1,
            'name' => 'Web Developer',
            'username' => 'webdev',
            'image' => 'default.png',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'position' => 'Super Admin',
            'doctor_id' => 1,
            'isWebDev' => true,
        ]);
        User::firstOrCreate([
            'id' => 2,
            'name' => 'Vibol Chan Setey Mungkul',
            'username' => 'mungkul',
            'image' => 'default.png',
            'password' => '$2y$10$YsiK1JXueoaXVoac1iSYT./ZNQW/K7WRYZL/JRLJ0EUG/Z.bL0xS2', // 12345678
            'position' => 'Admin',
            'doctor_id' => 1,
            'isWebDev' => true,
        ]);
        User::firstOrCreate([
            'id' => 3,
            'name' => 'Sokun Sovisal',
            'username' => 'sovisal',
            'image' => 'default.png',
            'password' => '$2y$10$YsiK1JXueoaXVoac1iSYT./ZNQW/K7WRYZL/JRLJ0EUG/Z.bL0xS2', // 12345678
            'position' => 'Admin',
            'doctor_id' => 1,
            'isWebDev' => true,
        ]);
        User::firstOrCreate([
            'id' => 4,
            'name' => 'Socheatha Tey',
            'username' => 'socheatha',
            'image' => 'default.png',
            'password' => '$2y$10$YsiK1JXueoaXVoac1iSYT./ZNQW/K7WRYZL/JRLJ0EUG/Z.bL0xS2', // 12345678
            'position' => 'Admin',
            'doctor_id' => 1,
            'isWebDev' => true,
        ]);
        User::firstOrCreate([
            'id' => 5,
            'name' => 'Dim Phallin',
            'username' => 'dimphallin',
            'image' => 'default.png',
            'password' => '$2y$10$YsiK1JXueoaXVoac1iSYT./ZNQW/K7WRYZL/JRLJ0EUG/Z.bL0xS2', // 12345678
            'position' => 'Admin',
            'doctor_id' => 1,
            'isWebDev' => 0,
        ]);
        
        if ($user = User::find(5)) {
            $user->assignRole('Admin');
        } 

    //    User::factory(5)->create();
    }
}
