<?php
namespace Conradoojr\ThiefLinks\Crawler;

interface ICrawler {
    public function getAllLinks($seasonNumber);
    public function getLinkEpisode($url);
    public function getSeasonLinks();
    public function getEpisodesLinks($urlSeason, $seasonNumber);
    public function getClassName();
}
