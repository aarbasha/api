<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\File;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Categories =  json_decode(File::get(database_path('JSON/Categories.json')), true);

        foreach ($Categories as $Categorie) {
            Categorie::create([
                'name' => $Categorie['name'],
                'path' => $Categorie['path'],
                'info' => $Categorie['info'],
                'icon' => $Categorie['icon'],
                'cover' => $Categorie['cover'],
                'parent_id' => $Categorie['parent_id'],
            ]);
        }
    }
}
