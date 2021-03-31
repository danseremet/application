<?php

namespace Database\Seeders;

use App\Models\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        # Adding the default application logo
        # and creating a symbolic link to for static content that uses it.
        $default_app_logo = '/storage/logos/default_app_logo.png';
        Settings::updateOrCreate(
            ['slug' => 'app_logo'],
            ['data' => ['path' => $default_app_logo]]
        );

        $base_dir = getcwd();
        chdir('storage/app/public/logos/');
        $target = 'default_app_logo.png';
        $link = 'app_logo.png';
        if (file_exists($link)) {
            unlink($link);
        }
        symlink($target, $link);
        chdir($base_dir);
    }
}
