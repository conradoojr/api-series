<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Conradoojr\ThiefLinks\Factory;
use Illuminate\Support\Facades\Request;
class ApiController extends Controller
{

    public function searchSerie($term)
    {
        $term = urldecode( $term );
        $factory = new Factory();
        $results = $factory->search($term);
        return response()->json($results);
    }

    public function getSeasons($urlSerie)
    {
        $url = urldecode($urlSerie);
        $factory = new Factory();
        $crawler = $factory->createCrawler($url);
        $result = $crawler->getSeasonLinks();
        return response()->json($result);
    }

    public function getEpisodes($urlSeason, $seasonNumber)
    {
        $url = urldecode($urlSeason);
        $factory = new Factory();
        $crawler = $factory->createCrawler($url);
        $result = $crawler->getEpisodesLinks($url, $seasonNumber);
        return response()->json($result);
    }

}
