<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScraperController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/scrapers/{id}",
     *      operationId="getProjectsList",
     *      tags={"scrapers"},
     *      summary="Launch a scraper",
     *      description="Lunch a scraper for get data from sources",
     *      @OA\Parameter(
     *          in="path",
     *          name="id",
     *          description="Scraper id to run",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *          style="form",
     *      examples = {
     *          "scrapers": {
     *              "summary": "El Tiempo",
     *              "value": 1
     *          },
     *          {
     *              "summary": "Tu Tiempo",
     *              "value": 2
     *          },
     *      }
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Not Found"),
     *      )
     *
     * Returns HTTP status.
     */
    function scrapers(Request $request)
    {
        $id = $request->id;
        $script = config('python.scripts') . 'scraper_' . $id . '.py';
        foreach (executePython($script, $request) as $result) {
            $data = json_decode($result, true);

            if ($id == 1 or $id == 2) {
                WeatherController::insert($data);
            }
        }

        return response('Execute complete', 200);
    }
}
