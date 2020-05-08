<?php

use App\Models\Fligths\FlightStatuses;
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
        FlightStatuses::create(['id' => 0, 'name' => "Landed"]);
        FlightStatuses::create(['id' => 1, 'name' => "Landed Late"]);
        FlightStatuses::create(['id' => 2, 'name' => "Cancelled"]);
        FlightStatuses::create(['id' => 3, 'name' => "Scheduled"]);
        FlightStatuses::create(['id' => 4, 'name' => "Unknown"]);
        FlightStatuses::create(['id' => 5, 'name' => "Diverted"]);
        FlightStatuses::create(['id' => 6, 'name' => "En-Route"]);
        FlightStatuses::create(['id' => 7, 'name' => "Others"]);
    }
}
