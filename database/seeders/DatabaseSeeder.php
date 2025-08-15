<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LaratrustSeeder::class);
        $this->call(PublicSeeder::class);
        $this->call(CategoriesSeeder::class);
        $this->call(UsersSeeder::class);

       // \App\Models\Post::factory(100)->create();

        // \App\Models\User::factory(200)->create();
    }
}
