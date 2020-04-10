<?php

use App\Models\Weather\WindDirection;
use Illuminate\Database\Seeder;

class WindDirectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WindDirection::create(['id' => 1, 'direction' => 'En calma', 'diminutive' => '-']);
        WindDirection::create(['id' => 2, 'direction' => 'Norte', 'diminutive' => 'N']);
        WindDirection::create(['id' => 3, 'direction' => 'Nordeste', 'diminutive' => 'NE']);
        WindDirection::create(['id' => 4, 'direction' => 'Este', 'diminutive' => 'E']);
        WindDirection::create(['id' => 5, 'direction' => 'Sureste', 'diminutive' => 'SE']);
        WindDirection::create(['id' => 6, 'direction' => 'Sur', 'diminutive' => 'S']);
        WindDirection::create(['id' => 7, 'direction' => 'Suroeste', 'diminutive' => 'SO']);
        WindDirection::create(['id' => 8, 'direction' => 'Oeste', 'diminutive' => 'O']);
        WindDirection::create(['id' => 9, 'direction' => 'Noroeste', 'diminutive' => 'NO']);
        WindDirection::create(['id' => 10, 'direction' => 'Variable', 'diminutive' => '*']);
    }
}
