<?php

namespace Database\Seeders;

use App\Models\User;
use Laratrust\Models\Role;
use Illuminate\Database\Seeder;
use Laratrust\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = ['ff9d00', '00ad65', '00b8c7', '008aeb', '0060ff', '6c00ff', 'fd00ff', 'ff0020', 'ff7d6e', 'ff7724', 'ee8700', '00bad8', '000000', '254abd', 'c61480', '00baff', '6a6aba', '3cbfdc', 'ff60bb'];

        $XColor = $colors[array_rand($colors)];


        $permissions = Permission::all();
        $owner = Role::find(1); // owner
        $admin = Role::find(2); // admin
        $user = Role::find(3); //user

        $owner->syncPermissions($permissions);



        User::create([
            'name' => 'Anas',
            'username' => 'aarbasha',
            "email_verify" => true,
            "phone_verify" => true,
            'email' => 'info@anasarbasha.net',
            'password' => bcrypt('$$XBOX_SX$$'),
            'color' => '000000'
        ])->addRole($owner);

        User::create([
            'name' => 'Anas',
            'username' => 'aarbasha_de',
            "email_verify" => true,
            "phone_verify" => true,
            'email' => 'anas.arbasha.deu@gmail.com',
            'password' => bcrypt('12345678'),
            'color' => $XColor
        ])->addRole($admin);

        User::create([
            'name' => 'user',
            'username' => 'user',
            "email_verify" => true,
            "phone_verify" => true,
            'email' => 'user@laravel.com',
            'password' => bcrypt('12345678'),
            'color' => $XColor
        ])->addRole($user);
    }
}
