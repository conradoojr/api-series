<?php
namespace Conradoojr\ThiefLinks\Crawler;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use RuntimeException;
use Conradoojr\ThiefLinks\Url;

class SeriesOnlineHd extends BaseCrawler
{
    public function __construct($siteUrl)
    {
        parent::__construct($siteUrl);
    }

    public function getClassName()
    {
        return str_replace('\\','',str_replace(__NAMESPACE__, '', __CLASS__));
    }

    public function getSeasonLinks()
    {
        $client = new Client([ 'base_uri' => $this->url->domain ]);
        $response = $client->request('GET', $this->url->path);
        $htmlSite = (string)$response->getBody();

        if($response->getStatusCode() != 200) {
            throw new RuntimeException('Status code different of 200');
        }

        $crawler = new DomCrawler($htmlSite);

        $filter = $crawler->filter('.tab-ep-list');
        $result = [];

        $currentSeasonNumber = 0;

        if (iterator_count($filter) > 0) {
            foreach ($filter as $i => $content) {
                $cralwer = new DomCrawler($content);
                $tds = $cralwer->filter('td');
                foreach ($tds as $key => $td) {
                    $cralwer = new DomCrawler($td);
                    $currentClass = $cralwer->attr('class');

                    //get seasons
                    if ($currentClass == 'ep-ntp') {
                        $currentSeasonNumber = (int)$cralwer->text();

                        if ($currentSeasonNumber != 0 && !array_key_exists($currentSeasonNumber, $result)) {
                            $urlSeasonWithNumber = urlencode(trim($this->url->domain . $this->url->path));
                            $result[$currentSeasonNumber] = '/episodes/' . $urlSeasonWithNumber . '/' . $currentSeasonNumber;
                        }
                    }

                }
            }
        }

        return $result;
    }

    public function getEpisodesLinks($urlSeason, $seasonNumber)
    {
        $url = new Url();
        $url->fill($urlSeason);
        $client = new Client([ 'base_uri' => $this->url->domain ]);
        $response = $client->request('GET', $this->url->path);
        $htmlSite = (string)$response->getBody();

        if($response->getStatusCode() != 200) {
            throw new RuntimeException('Status code different of 200');
        }

        $crawler = new DomCrawler($htmlSite);

        $filter = $crawler->filter('.tab-ep-list');
        $result = [];

        $currentSeasonNumber = 0;
        $episodeNumber = 0;
        $linkWithSubTitle = '';
        $linkWithVoiced ='';

        if (iterator_count($filter) > 0) {
            foreach ($filter as $i => $content) {
                $cralwer = new DomCrawler($content);
                $tds = $cralwer->filter('td');
                foreach ($tds as $key => $td) {
                    $cralwer = new DomCrawler($td);
                    $currentClass = $cralwer->attr('class');

                    //get seasons
                    if ($currentClass == 'ep-ntp') {
                        $currentSeasonNumber = (int)$cralwer->text();

                        if ($currentSeasonNumber == $seasonNumber && !array_key_exists($seasonNumber, $result)) {
                            $result[$seasonNumber] = [];
                        }
                    }

                    //get episodes
                    if ($currentClass == 'ep-nep') {
                        $episodeNumber = (int)filter_var($cralwer->text(), FILTER_SANITIZE_NUMBER_INT);

                        if ($episodeNumber!= 0 && $currentSeasonNumber == $seasonNumber) {
                            $result[$seasonNumber][$episodeNumber] = [];
                        }
                    }

                    //get link of voiced episode
                    if ($currentClass == 'ep-dub' && count($cralwer->filter('a')) > 0  && $currentSeasonNumber == $seasonNumber) {
                        // $linkWithVoiced = urlencode(trim(explode($this->url->domain, $cralwer->filter('a')->attr('href'))[1]));
                        $linkWithVoiced = trim($cralwer->filter('a')->attr('href'));
                        if ($linkWithVoiced != '') {
                            $result[$seasonNumber][$episodeNumber]['dublado'] = $this->getLinkEpisode($linkWithVoiced);
                        }
                    }

                    //get link of episode with subtitle
                    if ($currentClass == 'ep-leg' && count($cralwer->filter('a')) > 0  && $currentSeasonNumber == $seasonNumber) {
                        // $linkWithSubTitle = urlencode(trim(explode($this->url->domain, $cralwer->filter('a')->attr('href'))[1]));
                        $linkWithSubTitle = trim($cralwer->filter('a')->attr('href'));
                        if ($linkWithSubTitle != '') {
                            $result[$seasonNumber][$episodeNumber]['legendado'] = $this->getLinkEpisode($linkWithSubTitle);
                        }
                    }
                }
            }
        }
        else {
            throw new RuntimeException('Got empty result processing the dataset!');
        }
        return $result;
    }

    /**
     * Get all seasons with all epsisodes
     * @return [array] [array of seasons with episodes]
     */
    public function getAllLinks($seasonNumber)
    {
        $client = new Client([ 'base_uri' => $this->url->domain ]);
        $response = $client->request('GET', $this->url->path);
        $htmlSite = (string)$response->getBody();

        if($response->getStatusCode() != 200) {
            throw new RuntimeException('Status code different of 200');
        }

        $crawler = new DomCrawler($htmlSite);

        $filter = $crawler->filter('.tab-ep-list');
        $result = [];

        $currentSeasonNumber = 0;
        $episodeNumber = 0;
        $linkWithSubTitle = '';
        $linkWithVoiced ='';

        if (iterator_count($filter) > 0) {
            foreach ($filter as $i => $content) {
                $cralwer = new DomCrawler($content);
                $tds = $cralwer->filter('td');
                foreach ($tds as $key => $td) {
                    $cralwer = new DomCrawler($td);
                    $currentClass = $cralwer->attr('class');

                    //get seasons
                    if ($currentClass == 'ep-ntp') {
                        $currentSeasonNumber = (int)$cralwer->text();

                        if ($currentSeasonNumber == $seasonNumber && !array_key_exists($seasonNumber, $result)) {
                            $result[$seasonNumber] = [];
                        }
                    }

                    //get episodes
                    if ($currentClass == 'ep-nep') {
                        $episodeNumber = (int)filter_var($cralwer->text(), FILTER_SANITIZE_NUMBER_INT);

                        if ($episodeNumber!= 0 && $currentSeasonNumber == $seasonNumber) {
                            $result[$seasonNumber][$episodeNumber] = [];
                        }
                    }

                    //get link of voiced episode
                    if ($currentClass == 'ep-dub' && count($cralwer->filter('a')) > 0  && $currentSeasonNumber == $seasonNumber) {
                        // $linkWithVoiced = urlencode(trim(explode($this->url->domain, $cralwer->filter('a')->attr('href'))[1]));
                        $linkWithVoiced = trim($cralwer->filter('a')->attr('href'));
                        if ($linkWithVoiced != '') {
                            $result[$seasonNumber][$episodeNumber]['dublado'] = $this->getLinkEpisode($linkWithVoiced);
                        }
                    }

                    //get link of episode with subtitle
                    if ($currentClass == 'ep-leg' && count($cralwer->filter('a')) > 0  && $currentSeasonNumber == $seasonNumber) {
                        // $linkWithSubTitle = urlencode(trim(explode($this->url->domain, $cralwer->filter('a')->attr('href'))[1]));
                        $linkWithSubTitle = trim($cralwer->filter('a')->attr('href'));
                        if ($linkWithSubTitle != '') {
                            $result[$seasonNumber][$episodeNumber]['legendado'] = $this->getLinkEpisode($linkWithSubTitle);
                        }
                    }
                }
            }
        }
        else {
            throw new RuntimeException('Got empty result processing the dataset!');
        }

        return $result;
    }

    public function getLinkEpisode($url)
    {
        $urlEpisode = new Url();
        $urlEpisode->fill($url);
        $pathEpisode = $urlEpisode->path . '?'. $urlEpisode->query;

        $client = new Client([ 'base_uri' => $urlEpisode->domain ]);
        $response = $client->request('GET', $pathEpisode);
        $htmlSite = (string)$response->getBody();

        if($response->getStatusCode() != 200) {
            throw new RuntimeException('Status code different of 200');
        }

        $playersUnavailable = [
            'hd',
            'vidto',
            'vidzi'
        ];

        $crawler = new DomCrawler($htmlSite);
        $filter = $crawler->filter('ul.player-opcoes li');
        $result = [];
        if (iterator_count($filter) > 0) {
            foreach ($filter as $i => $content) {
                $cralwer = new DomCrawler($content);
                $player = strtolower($cralwer->text());
                if ( in_array($player, $playersUnavailable)) {
                    continue;
                }

                $pathAndQuery = explode($this->url->domain, $cralwer->filter('a')->attr('href'))[1];
                $result[] = $this->{'getPlayerLink'.$player}($pathAndQuery);
            }
        }
        return $result;
    }

    private function getPlayerLinkOpenload($linkEpisode)
    {
        $client = new Client([ 'base_uri' => $this->url->domain ]);
        $response = $client->request('GET', $linkEpisode);
        $htmlSite = (string)$response->getBody();

        if($response->getStatusCode() != 200) {
            throw new RuntimeException('Status code different of 200');
        }

        $crawler = new DomCrawler($htmlSite);
        $filter = $crawler->filter('iframe.ps-iframe');

        // $embedLink = new URL();
        // $embedLink->fill($filter->attr('src'));

        // $client = new Client([ 'base_uri' => $embedLink->domain ]);
        // $response = $client->request('GET', $embedLink->path);
        // $htmlSite = (string)$response->getBody();

        // print_r($htmlSite);
        // exit('<br />;)');
        return $filter->attr('src');
    }
    private function getPlayerLinkPrincipal($linkEpisode)
    {
        $client = new Client([ 'base_uri' => $this->url->domain ]);
        $response = $client->request('GET', $linkEpisode);
        $htmlSite = (string)$response->getBody();

        if($response->getStatusCode() != 200) {
            throw new RuntimeException('Status code different of 200');
        }

        $crawler = new DomCrawler($htmlSite);
        $filter = $crawler->filter('iframe.ps-iframe');
        return $filter->attr('src');
    }

    public function search($term)
    {
        $indexOfResult = $this->getClassName();
        $result = [];
        $client = new Client([ 'base_uri' => $this->url->domain ]);
        $response = $client->request('GET', '/', ['query' => ['s' => $term]]);
        $htmlSite = (string)$response->getBody();

        if($response->getStatusCode() != 200) {
            throw new RuntimeException('Status code different of 200');
        }
        $crawler = new DomCrawler($htmlSite);

        $i = 0;
        $lis = $crawler->filter('ul.post li');
        foreach ($lis as $ii => $li) {
            $li = new DomCrawler($li);

            if (count($li->children()) == 0) {
                continue;
            }

            $result[$i]['title'] = trim($li->filter('h2')->text());
            $result[$i]['image'] = trim($li->filter('img')->attr('src'));
            $result[$i]['link']  = '/seasons/' . urlencode(trim($li->filter('a')->attr('href')));
            $result[$i]['type']  = strtolower(trim($li->filter('.calidad')->text()));
            $i++;
        }
        return $result;
    }
}
