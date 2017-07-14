<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$app->group(['middleware' => 'Cors'], function () use ($app) {

    $app->get('/', function () use ($app) {
        return $app->version();
    });

    $app->get('serie','ApiController@getSerie');
    $app->get('search/{serieName}','ApiController@searchSerie');
    $app->get('seasons/{urlSerie}','ApiController@getSeasons');
    $app->get('episodes/{urlSeason}/{seasonNumber}','ApiController@getEpisodes');
});
