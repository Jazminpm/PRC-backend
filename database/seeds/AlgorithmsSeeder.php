<?php

use App\Models\Models\Algorithms;
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
        Algorithms::create(['id' => 0, 'name' => "Naive Bayes", "description" => 'It is a supervised classification and prediction technique that classifies a case maximizing the probability that it will occur under certain conditions. For example, if we want to classify whether we have a pair or a trio in our deck, the algorithm will determine what we are most likely to find in each of our 5-card hands. With Bayes\' Theorem we can find the probability that A (hypothesis) will occur, given that B (evidence) has occurred.']);
        Algorithms::create(['id' => 1, 'name' => "Random Forest", "description" => 'Random forest is an Artificial Intelligence algorithm that combines many independent decision trees on random data sets with the same distribution.']);
        Algorithms::create(['id' => 2, 'name' => "Gradient Boosting", "description" => 'Gradient Boosted Tree produces a predictive model in the form of a set of weak decision trees. Construct the model in a staggered fashion like other boosting methods do, and generalize them by allowing arbitrary optimization of a differentiable loss function.']);
        Algorithms::create(['id' => 3, 'name' => "Decision Tree", "description" => 'The goal of this algorithm is to find the simplest tree that best separates the examples. This algorithm is used for classification and regression. It is a supervised learning system and when implemented the "divide and conquer" strategy is applied.']);
        Algorithms::create(['id' => 4, 'name' => "k-nn", "description" => 'The k-NN algorithm is based on comparing an unknown example with the training examples that are the closest neighbors of the unknown example.']);
        Algorithms::create(['id' => 5, 'name' => "Logistic Regression", "description" => 'Logistic regression is a statistical method of analyzing a data set in which there are one or more independent variables that determine an outcome. The result is measured with a binomial variable Predicts the probability of occurrence of an event by fitting the data to a logit function.']);
    }
}
