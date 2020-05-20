<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ModelController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/models/training",
     *      operationId="getTrainingModel",
     *      tags={"models"},
     *      summary="Training Model",
     *      description="Create a training model with the data between a start date and and end date.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="characteristic",
     *                      type="array",@OA\Items(type="string"),
     *                      description="Characteristics selected for the training model"
     *                  ),
     *                  @OA\Property(
     *                      property="start_date",
     *                      type="date",
     *                      description="Date in format Y-m-d"
     *                  ),
     *                  @OA\Property(
     *                      property="end_date",
     *                      type="date",
     *                      description="Date in format Y-m-d"
     *                  ),
     *                  @OA\Property(
     *                      property="algorithm_id",
     *                      type="int",
     *                      description="Algorithm id selected for the training"
     *                  ),
     *                  example={"characteristic": {"id","date", "time","airline_id","city_id","airport_id","delay",
     *                          "temperature","humidity","pressure","wind_direction","wind_speed"},
     *                          "start_date": "2020-04-20","end_date": "2020-04-24","algorithm_id":1}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Ok."
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="errors",
     *                          type="array",
     *                          description="List of errors.",
     *                          @OA\Items(type="string")
     *                      ),
     *                      example={
     *                          "errors": {
     *                              "The characteristic field is required.",
     *                              "The start date field is required.",
     *                              "The end date field is required.",
     *                              "The algorithm id field is required.",
     *                              "The characteristic must be an array.",
     *                              "The start date is not a valid date.",
     *                              "The end date is not a valid date.",
     *                              "The start date does not match the format Y-m-d.",
     *                              "The end date does not match the format Y-m-d.",
     *                              "The algorithm id must be an integer.",
     *                              "The start date must be a date before or equal to yesterday.",
     *                              "The end date must be a date before or equal to today.",
     *                              "The selected algorithm id is invalid.",
     *                              "Not all characteristic exists.",
     *                              "There are no registered flights for these dates."
     *                          }
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="message",
     *                          type="string",
     *                          description="Server message that contains the error."
     *                      ),
     *                      @OA\Property(
     *                          property="exception",
     *                          type="string",
     *                          description="Generated exception."
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          type="string",
     *                          description="File that throw the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="line",
     *                          type="integer",
     *                          description="Line that thorws the execption."
     *                      ),
     *                      @OA\Property(
     *                          property="trace",
     *                          type="array",
     *                          description="Trace route objects.",
     *                          @OA\Items(type="object")
     *                      ),
     *                      example={
     *                          "messagge": "The command failed.",
     *                          "exception": "",
     *                          "file": "",
     *                          "line": 150,
     *                          "trace": {"file":"", "line":1, "content":""}
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     *
     * @param Request $request
     * @return string
     */
    function trainingModel(Request $request)
    {
        $date = new DateTime('now');
        $dateStr = $date->format('Y-m-d H:i:s');
        $validator = Validator::make($request->json()->all(), [
            'characteristic' => ['required', 'array'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:yesterday'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'algorithm_id' => ['required', 'integer', 'exists:algorithms,id']
        ]);

        if ($validator->fails()) {
            $validation = failValidation($validator);
            $message = "The Train Model launched at ".$dateStr." did not finished.
            The errors have been:";
            MailController::emailErrors($validation, $dateStr, $message);
            return $validation;
        } else {
            foreach ($request->characteristic as $key => $val) {
                if (!Schema::hasColumn('flights', $val) and !Schema::hasColumn('weathers', $val) and
                    $val != 'date' and $val != 'time') {
                    return response()->json(["errors" => 'Not all characteristic exists.'],
                        JsonResponse::HTTP_BAD_REQUEST);
                }
            }

            $characteristic = $request->characteristic;
            if (in_array("airport_id", $characteristic)) {
                $pos = array_keys($characteristic, "airport_id")[0];
                $characteristic[$pos] = "airport_id";
            } else {
                array_push($characteristic, "airport_id");
            }

            $args = FlightsController::getModelDataTrain($characteristic, $request->start_date, $request->end_date);
            if (sizeof($args) > 0) {
                ModelController::export($args, $characteristic, '../storage/modelsData/dataTrain.csv');
                $airports['airport_id'] = [];
                foreach ($args as $arg) {
                    array_push($airports['airport_id'], $arg['airport_id']);
                }
                $airports = collect(array_unique($airports['airport_id']))->implode('-');

                $args = $request->all();
                $script = config('python.scripts') . 'model_1.py';

                $data = json_decode(executePython($script, $args)[0], true);
                $data['airports'] = $airports;
                DB::table('models')->Insert($data); // Insert data in the BBDD

                $algorithmName = DB::table('algorithms')->select(['name'])->where('id', $request->algorithm_id)->first()->name;
                $message = "The Train Model launched at ".$dateStr." of the ".$algorithmName." algorithm has already finished.";
                MailController::sendMailScrapers($date, $message,'Train Model finished');
                return response()->json([], JsonResponse::HTTP_NO_CONTENT);
            } else {
                return response()->json(["errors" => 'There are no registered flights for these dates.'],
                    JsonResponse::HTTP_BAD_REQUEST);
            }
        }

    }

    /**
     * @OA\Post(
     *      path="/api/models/predict",
     *      operationId="getPredictModel",
     *      tags={"models"},
     *      summary="Predict flight data",
     *      description="This function is used to predict flight data.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="start_date",
     *                      type="date",
     *                      description="Date in format Y-m-d"
     *                  ),
     *                  @OA\Property(
     *                      property="end_date",
     *                      type="date",
     *                      description="Date in format Y-m-d"
     *                  ),
     *                  example={"start_date": "2020-04-25","end_date": "2020-04-26"}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="total",
     *                          type="integer",
     *                          description="Total inserted documents"
     *                      ),
     *                      example={
     *                          "total": 20,
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="errors",
     *                          type="array",
     *                          description="List of errors.",
     *                          @OA\Items(type="string")
     *                      ),
     *                      example={
     *                          "errors": {
     *                              "The start date field is required.",
     *                              "The end date field is required.",
     *                              "The start date is not a valid date.",
     *                              "The end date is not a valid date.",
     *                              "The start date does not match the format Y-m-d.",
     *                              "The end date does not match the format Y-m-d.",
     *                              "The start date must be a date before or equal to today.",
     *                              "The end date must be a date before or equal to today.",
     *                              "There are no registered flights for these dates."
     *                          }
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="message",
     *                          type="string",
     *                          description="Server message that contains the error."
     *                      ),
     *                      @OA\Property(
     *                          property="exception",
     *                          type="string",
     *                          description="Generated exception."
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          type="string",
     *                          description="File that throw the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="line",
     *                          type="integer",
     *                          description="Line that thorws the execption."
     *                      ),
     *                      @OA\Property(
     *                          property="trace",
     *                          type="array",
     *                          description="Trace route objects.",
     *                          @OA\Items(type="object")
     *                      ),
     *                      example={
     *                          "messagge": "The command failed.",
     *                          "exception": "",
     *                          "file": "",
     *                          "line": 150,
     *                          "trace": {"file":"", "line":1, "content":""}
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     *
     * @param Request $request
     * @return string
     */
    function predictModel(Request $request)
    {
        $date = new DateTime('now');
        $dateStr = $date->format('Y-m-d H:i:s');
        $validator = Validator::make($request->json()->all(), [
            'start_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today']
        ]);

        if ($validator->fails()) {
            $validation = failValidation($validator);
            $message = "The prediction launched at ".$dateStr." did not finished.
            The errors have been:";
            MailController::emailErrors($validation, $dateStr, $message);
            return $validation;
        } else {
            $selectedModel = ModelController::selectedModel();

            $airports = array_map('intval', explode('-', $selectedModel->airports));
            $characteristic = ModelController::getCharacteristic($selectedModel);

            $args = FlightsController::getModelDataPredict($characteristic, $request->start_date, $request->end_date, $airports);

            if (sizeof($args) > 0) {
                modelController::export($args, $characteristic, '../storage/modelsData/dataPredict.csv');

                $data = $request->all();
                array_push($data, $selectedModel->type);
                array_push($data, $selectedModel->date);
                $script = config('python.scripts') . 'model_2.py';

                $result = executePython($script, $data);
                preg_match_all('!\d!', $result[0], $matches);
                $inserts = 0;
                for ($i = 0; $i < sizeof($matches[0]); $i++) {
                    $args[$i]['prediction'] =  $matches[0][$i];
                    FlightsController::updatePrediction($args[$i]);
                    $inserts += 1;
                }

                $algorithmName = DB::table('algorithms')->select(['name'])->where('id', $request->algorithm_id)->first()->name;
                $message = "The prediction launched at ".$dateStr." has already finished.";
                MailController::sendMailScrapers($date, $message,'Prediction finished');
                return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
            } else {
                return response()->json(["errors" => 'There are no registered flights for these dates.'],
                    JsonResponse::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @OA\Post(
     *      path="/api/models/updateModel",
     *      operationId="setSelectedModel",
     *      tags={"models"},
     *      summary="Select model in use",
     *      description="It is used to select the model that is used in the application.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="model",
     *                      type="int",
     *                  ),
     *                  example={"model":1}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Ok."
     *      ),
     *     @OA\Response(
     *          response=400,
     *          description="Bad request.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="errors",
     *                          type="array",
     *                          description="List of errors.",
     *                          @OA\Items(type="string")
     *                      ),
     *                      example={
     *                          "errors": {
     *                              "The model id field is required.",
     *                              "The model id must be an integer.",
     *                              "The selected model id is invalid.",
     *                          }
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="message",
     *                          type="string",
     *                          description="Server message that contains the error."
     *                      ),
     *                      @OA\Property(
     *                          property="exception",
     *                          type="string",
     *                          description="Generated exception."
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          type="string",
     *                          description="File that throw the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="line",
     *                          type="integer",
     *                          description="Line that thorws the execption."
     *                      ),
     *                      @OA\Property(
     *                          property="trace",
     *                          type="array",
     *                          description="Trace route objects.",
     *                          @OA\Items(type="object")
     *                      ),
     *                      example={
     *                          "messagge": "The command failed.",
     *                          "exception": "",
     *                          "file": "",
     *                          "line": 150,
     *                          "trace": {"file":"", "line":1, "content":""}
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     *
     * @param Request $request
     * @return string
     */
    function updateModelInUse(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'model' => ['required', 'integer', 'exists:models,id']
        ]);

        if ($validator->fails()) {
            return failValidation($validator);
        } else {
            $model = DB::table('in_uses')->select('model')->first();
            if (is_null($model)) {
                DB::table('in_uses')->insert(['model' => $request->model, 'analysis' => 0]);
            } else {
                DB::table('in_uses')->where('model', $model->model)
                    ->update(['model' => $request->model]);
            }
        }

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Post(
     *      path="/api/models/algorithms",
     *      operationId="getAlgorithms",
     *      tags={"models"},
     *      summary="Get all algorithms",
     *      description="It is used to get all information of each algorithm of the database.",
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="response",
     *                          type="array",@OA\Items(type="json"),
     *                          description="All algorithms"
     *                      ),
     *                      example={{"id": 0,
     *                              "name": "Naive Bayes",
     *                              "description": "It is a supervised classification and prediction technique that classifies a case maximizing the probability that it will occur under certain conditions. For example, if we want to classify whether we have a pair or a trio in our deck, the algorithm will determine what we are most likely to find in each of our 5-card hands. With Bayes' Theorem we can find the probability that A (hypothesis) will occur, given that B (evidence) has occurred."},
     *                              {"id": 1,
     *                              "name": "Random Forest",
     *                              "description": "Random forest is an Artificial Intelligence algorithm that combines many independent decision trees on random data sets with the same distribution."},
     *                              {"id": 2,
     *                              "name": "Gradient Boosting",
     *                              "description": "Gradient Boosted Tree produces a predictive model in the form of a set of weak decision trees. Construct the model in a staggered fashion like other boosting methods do, and generalize them by allowing arbitrary optimization of a differentiable loss function."},
     *                              {"id": 3,
     *                              "name": "Decision Tree",
     *                              "description": "The goal of this algorithm is to find the simplest tree that best separates the examples. This algorithm is used for classification and regression. It is a supervised learning system and when implemented the 'divide and conquer' strategy is applied."},
     *                             {"id": 4,
     *                              "name": "k-nn",
     *                              "description": "The k-NN algorithm is based on comparing an unknown example with the training examples that are the closest neighbors of the unknown example."},
     *                              {"id": 5,
     *                              "name": "Logistic Regression",
     *                              "description": "Logistic regression is a statistical method of analyzing a data set in which there are one or more independent variables that determine an outcome. The result is measured with a binomial variable Predicts the probability of occurrence of an event by fitting the data to a logit function."}
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="message",
     *                          type="string",
     *                          description="Server message that contains the error."
     *                      ),
     *                      @OA\Property(
     *                          property="exception",
     *                          type="string",
     *                          description="Generated exception."
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          type="string",
     *                          description="File that throw the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="line",
     *                          type="integer",
     *                          description="Line that thorws the execption."
     *                      ),
     *                      @OA\Property(
     *                          property="trace",
     *                          type="array",
     *                          description="Trace route objects.",
     *                          @OA\Items(type="object")
     *                      ),
     *                      example={
     *                          "messagge": "The command failed.",
     *                          "exception": "",
     *                          "file": "",
     *                          "line": 150,
     *                          "trace": {"file":"", "line":1, "content":""}
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     *
     * @param Request $request
     * @return string
     */
    function getAlgorithms()
    {
        return response()->json(json_decode(DB::table('algorithms')->select('*')->get(),
            JsonResponse::HTTP_OK));
    }

    /**
     * @OA\Post(
     *      path="/api/models/models",
     *      operationId="getModels",
     *      tags={"models"},
     *      summary="Get all models",
     *      description="It is used to get all information of each model of the database.",
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="response",
     *                          type="array",@OA\Items(type="json"),
     *                          description="All models"
     *                      ),
     *                      example={{
     *                          "id": 1,
     *                          "type": 1,
     *                          "date": "2020-05-05 01:34:45",
     *                          "report_num_rows": 179,
     *                          "report_precision_0": "1.00",
     *                          "report_precision_1": "1.00",
     *                          "report_recall_0": "1.00",
     *                          "report_recall_1": "1.00",
     *                          "report_f1_score_0": "1.00",
     *                          "report_f1_score_1": "1.00",
     *                          "report_accuracy_precision": "1.00",
     *                          "report_accuracy_recall": "1.00",
     *                          "report_accuracy_f1_score": "1.00"
     *                          },
     *                          {
     *                          "id": 3,
     *                          "type": 2,
     *                          "date": "2020-05-08 23:55:21",
     *                          "report_num_rows": 27,
     *                          "report_precision_0": "1.00",
     *                          "report_precision_1": "1.00",
     *                          "report_recall_0": "1.00",
     *                          "report_recall_1": "1.00",
     *                          "report_f1_score_0": "1.00",
     *                          "report_f1_score_1": "1.00",
     *                          "report_accuracy_precision": "1.00",
     *                          "report_accuracy_recall": "1.00",
     *                          "report_accuracy_f1_score": "1.00"
     *                          },
     *                          {
     *                          "id": 4,
     *                          "type": 2,
     *                          "date": "2020-05-08 23:56:48",
     *                          "report_num_rows": 27,
     *                          "report_precision_0": "1.00",
     *                          "report_precision_1": "1.00",
     *                          "report_recall_0": "1.00",
     *                          "report_recall_1": "1.00",
     *                          "report_f1_score_0": "1.00",
     *                          "report_f1_score_1": "1.00",
     *                          "report_accuracy_precision": "1.00",
     *                          "report_accuracy_recall": "1.00",
     *                          "report_accuracy_f1_score": "1.00"
     *                          }
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="message",
     *                          type="string",
     *                          description="Server message that contains the error."
     *                      ),
     *                      @OA\Property(
     *                          property="exception",
     *                          type="string",
     *                          description="Generated exception."
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          type="string",
     *                          description="File that throw the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="line",
     *                          type="integer",
     *                          description="Line that thorws the execption."
     *                      ),
     *                      @OA\Property(
     *                          property="trace",
     *                          type="array",
     *                          description="Trace route objects.",
     *                          @OA\Items(type="object")
     *                      ),
     *                      example={
     *                          "messagge": "The command failed.",
     *                          "exception": "",
     *                          "file": "",
     *                          "line": 150,
     *                          "trace": {"file":"", "line":1, "content":""}
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     *
     * @param Request $request
     * @return string
     */
    function getModels()
    {
        $columns = ["id", "type", "date",
            "report_num_rows", "report_precision_0", "report_precision_1", "report_recall_0", "report_recall_1",
            "report_f1_score_0", "report_f1_score_1", "report_accuracy_precision", "report_accuracy_recall",
            "report_accuracy_f1_score"];
        return response()->json(json_decode(DB::table('models')->select($columns)->get(),
            JsonResponse::HTTP_OK));
    }

    /**
     * @OA\Post(
     *      path="/api/models/lastModels",
     *      operationId="getLastModels",
     *      tags={"models"},
     *      summary="Get info about last models",
     *      description="It is used to get all information for get the last models created in the database.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="model_id",
     *                      type="int",
     *                  ),
     *                  example={"model_id":3}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="response",
     *                          type="array",@OA\Items(type="json"),
     *                          description="All models"
     *                      ),
     *                      example={{
     *                          "id": 8,
     *                          "type": 1,
     *                          "date": "2020-05-05 01:34:45",
     *                          "report_num_rows": 179,
     *                          "report_precision_0": "1.00",
     *                          "report_precision_1": "1.00",
     *                          "report_recall_0": "1.00",
     *                          "report_recall_1": "1.00",
     *                          "report_f1_score_0": "1.00",
     *                          "report_f1_score_1": "1.00",
     *                          "report_accuracy_precision": "1.00",
     *                          "report_accuracy_recall": "1.00",
     *                          "report_accuracy_f1_score": "1.00"
     *                          },
     *                          {
     *                          "id": 9,
     *                          "type": 2,
     *                          "date": "2020-05-08 23:55:21",
     *                          "report_num_rows": 27,
     *                          "report_precision_0": "1.00",
     *                          "report_precision_1": "1.00",
     *                          "report_recall_0": "1.00",
     *                          "report_recall_1": "1.00",
     *                          "report_f1_score_0": "1.00",
     *                          "report_f1_score_1": "1.00",
     *                          "report_accuracy_precision": "1.00",
     *                          "report_accuracy_recall": "1.00",
     *                          "report_accuracy_f1_score": "1.00"
     *                          },
     *                          {
     *                          "id": 10,
     *                          "type": 2,
     *                          "date": "2020-05-08 23:56:48",
     *                          "report_num_rows": 27,
     *                          "report_precision_0": "1.00",
     *                          "report_precision_1": "1.00",
     *                          "report_recall_0": "1.00",
     *                          "report_recall_1": "1.00",
     *                          "report_f1_score_0": "1.00",
     *                          "report_f1_score_1": "1.00",
     *                          "report_accuracy_precision": "1.00",
     *                          "report_accuracy_recall": "1.00",
     *                          "report_accuracy_f1_score": "1.00"
     *                          }
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="message",
     *                          type="string",
     *                          description="Server message that contains the error."
     *                      ),
     *                      @OA\Property(
     *                          property="exception",
     *                          type="string",
     *                          description="Generated exception."
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          type="string",
     *                          description="File that throw the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="line",
     *                          type="integer",
     *                          description="Line that thorws the execption."
     *                      ),
     *                      @OA\Property(
     *                          property="trace",
     *                          type="array",
     *                          description="Trace route objects.",
     *                          @OA\Items(type="object")
     *                      ),
     *                      example={
     *                          "messagge": "The command failed.",
     *                          "exception": "",
     *                          "file": "",
     *                          "line": 150,
     *                          "trace": {"file":"", "line":1, "content":""}
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     *
     * @param Request $request
     * @return string
     */
    function getLastModels(Request $request){
        $columns = ["id", "type", "date",
            "report_num_rows", "report_precision_0", "report_precision_1", "report_recall_0", "report_recall_1",
            "report_f1_score_0", "report_f1_score_1", "report_accuracy_precision", "report_accuracy_recall",
            "report_accuracy_f1_score"];
        return response()->json(json_decode(DB::table('models')->select($columns)->
            where('id', '>' ,$request->model_id)->get(),
            JsonResponse::HTTP_OK));
    }

    /**
     * @OA\Post(
     *      path="/api/models/deleteModel",
     *      operationId="deleteModel",
     *      tags={"models"},
     *      summary="Delete a selected model",
     *      description="It is used for delete a model in the database.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="model_id",
     *                      type="int",
     *                  ),
     *                  example={"model_id":1}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Ok. No Content."
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="message",
     *                          type="string",
     *                          description="Server message that contains the error."
     *                      ),
     *                      @OA\Property(
     *                          property="exception",
     *                          type="string",
     *                          description="Generated exception."
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          type="string",
     *                          description="File that throw the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="line",
     *                          type="integer",
     *                          description="Line that thorws the execption."
     *                      ),
     *                      @OA\Property(
     *                          property="trace",
     *                          type="array",
     *                          description="Trace route objects.",
     *                          @OA\Items(type="object")
     *                      ),
     *                      example={
     *                          "messagge": "The command failed.",
     *                          "exception": "",
     *                          "file": "",
     *                          "line": 150,
     *                          "trace": {"file":"", "line":1, "content":""}
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     *
     * @param Request $request
     * @return string
     */
    function deleteModel(Request $request){
        DB::table('models')->where('id',$request->model_id)->delete();
        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    //OTRAS FUNCIONES
    function getCharacteristic($data)
    {
        $characteristic = [];

        array_push($characteristic, 'date');
        array_push($characteristic, 'time');
        array_push($characteristic, 'id');

        if ($data->attribute_airline == 1) {
            array_push($characteristic, 'airline_id');
        }
        if ($data->attribute_destination == 1) {
            array_push($characteristic, 'city_id');
        }
        if ($data->attribute_temperature == 1) {
            array_push($characteristic, 'temperature');
        }
        if ($data->attribute_humidity == 1) {
            array_push($characteristic, 'humidity');
        }
        if ($data->attribute_wind_speed == 1) {
            array_push($characteristic, 'wind_speed');
        }
        if ($data->attribute_wind_direction == 1) {
            array_push($characteristic, 'wind_direction');
        }
        if ($data->attribute_pressure == 1) {
            array_push($characteristic, 'pressure');
        }
        if ($data->attribute_airport_id == 1) {
            array_push($characteristic, 'airport_id');
        }

        return $characteristic;
    }

    function selectedModel()
    {
        $selectModel = DB::table('in_uses')->select('model')->first();
        if (!is_null($selectModel)) {
            $data = DB::table("models")->select(DB::raw('*'))
                ->where('id', $selectModel->model)->first();

            return $data;
        } else {
            return null;
        }
    }

    public function export($args, $characteristic, $name)
    {
        $fp = fopen($name, 'w');

        fputcsv($fp, $characteristic);
        foreach ($args as $campos) {
            fputcsv($fp, $campos);
        }

        fclose($fp);
    }
}

