<?php

use \App\Models\Users\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'id' => 1,
            'type' => 'admin'
        ]);

        Role::create([
            'id' => 2,
            'type' => 'client'
        ]);

        Role::create([
            'id' => 3,
            'type' => 'scraper'
        ]);
    }
}
