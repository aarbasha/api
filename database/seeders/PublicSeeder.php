<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PublicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $folders = ['images', 'photo', 'cover', 'avatars', "chat_media"];

        foreach ($folders as $folder) {
            $path = public_path($folder);

            if (!File::exists($path)) {
                File::makeDirectory($path, $mode = 0777, $recursive = true);
                $this->command->info("Created folder: {$path}");
            } else {
                $this->command->info("Folder already exists: {$path}");
            }
        }
    }
}
