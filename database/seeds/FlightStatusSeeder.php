<?php

use App\Models\Fligths\FlightStatus;
use Illuminate\Database\Seeder;

class FlightStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FlightStatus::create(['id' => 0, 'name' => "Landed"]);
        FlightStatus::create(['id' => 1, 'name' => "Landed Late"]);
        FlightStatus::create(['id' => 2, 'name' => "Cancelled"]);
        FlightStatus::create(['id' => 3, 'name' => "Scheduled"]);
        FlightStatus::create(['id' => 4, 'name' => "Unknown"]);
        FlightStatus::create(['id' => 5, 'name' => "Diverted"]);
        FlightStatus::create(['id' => 6, 'name' => "En-Route"]);
        FlightStatus::create(['id' => 7, 'name' => "Others"]);
    }
}
