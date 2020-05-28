<?php

use \App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'id' => 1,
            'dni' => '00000000T',
            'name' => 'Administrator',
            'surnames' => 'EasyTravel',
            'phoneNumber' => '910000000',
            'email' => 'admin@easytravel.com',
            'password' => bcrypt('admin@easytravel'),
            'role' => 1
        ]);

        User::create([
            'id' => 2,
            'dni' => '00000001R',
            'name' => 'Scraper',
            'surnames' => 'EasyTravel',
            'phoneNumber' => '910000000',
            'email' => 'scraper@easytravel.com',
            'password' => bcrypt('scraper@easytravel'),
            'role' => 3
        ]);

    }
}
