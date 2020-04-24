<?php

use App\Models\Fligths\Algorithms;
use Illuminate\Database\Seeder;

class AlgorithmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Algorithms::create(['id' => 0, 'name' => "Naive Bayes"]);
        Algorithms::create(['id' => 1, 'name' => "Random Forest"]);
        Algorithms::create(['id' => 2, 'name' => "Gradient Boosting"]);
        Algorithms::create(['id' => 3, 'name' => "Decision Tree"]);
        Algorithms::create(['id' => 4, 'name' => "k-nn"]);
        Algorithms::create(['id' => 5, 'name' => "Logistic Regression"]);

    }
}
