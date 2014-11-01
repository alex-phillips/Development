<?php
/**
 * Created by PhpStorm.
 * User: exonintrendo
 * Date: 9/27/14
 * Time: 10:55 PM
 */

class BuildPlexCommand extends \Primer\Console\BaseCommand
{
    private $_moviesSrc = 'http://127.0.0.1:32400/library/sections/1/all';
    private $_tvShowsSrc = 'http://127.0.0.1:32400/library/sections/2/all';
    private $_rottenTomatoesApi = 'http://api.rottentomatoes.com/api/public/v1.0/movies.json?apikey=4tcdm6p46y23yhkfzzu2nre5';

    public function configure()
    {
        $this->setName('plex:build');
        $this->setDescription("Build JSON encoded strucuture of movie information from the Plex API.");
    }

    public function run()
    {
        $this->buildMovies();
        $this->buildTvShows();
    }

    private function buildMovies()
    {
        $data = file_get_contents($this->_moviesSrc);
        $data = json_decode(json_encode((array)simplexml_load_string($data)));
        $movie_info = array();
        $count = 0;

        $progress = $this->getHelper('ProgressBar');
        $progress->initialize($this->getStdout(), count($data->Video));
        foreach($data->Video as $movie) {
            $progress->increment();
            $info = array();
            $info[] = (isset($movie->{"@attributes"}->title) ? $movie->{"@attributes"}->title : '');
            $info[] = (isset($movie->{"@attributes"}->year) ? $movie->{"@attributes"}->year : '');
            $info[] = (isset($movie->{"@attributes"}->contentRating) ? $movie->{"@attributes"}->contentRating : '');
            $info['poster'] = '';

            $api_information = json_decode(file_get_contents($this->_rottenTomatoesApi . '&q=' . str_replace(' ', '+', $movie->{"@attributes"}->title) . '&page_limit=1'));

            if (isset($api_information->movies[0]->posters->original)) {
                $image = $api_information->movies[0]->posters->detailed;
                $info['poster'] = $image;
            }

            // Adjust resolution string
            if (isset($movie->Media->{"@attributes"}->videoResolution)) {
                switch($movie->Media->{"@attributes"}->videoResolution) {
                    case 1080:
                        $info[] = '1080p';
                        break;
                    case 480:
                        $info[] = '480p';
                        break;
                    case 'sd':
                        $info[] = 'Standard Definition';
                        break;
                    case 576:
                        $info[] = '576p';
                        break;
                    default:
                        $info[] = '';
                        break;
                }
            }
            else {
                $info[] = '';
            }

            $info['all'] = $movie;

            $movie_info[] = $info;

            if ($count == 4) {
                $count = 0;
                sleep(1.5);
            }
            else {
                $count++;
            }
        }
        file_put_contents(APP_ROOT . '/public/content/movies.json', json_encode($movie_info));
    }

    private function buildTvShows()
    {
        $data = file_get_contents($this->_tvShowsSrc);
        $data = json_decode(json_encode((array)simplexml_load_string($data)));
        $show_info  = array();
        foreach($data->Directory as $show) {
            $info = array();
            $info[] = (isset($show->{"@attributes"}->title) ? $show->{"@attributes"}->title : '');
            $info[] = (isset($show->{"@attributes"}->leafCount) ? $show->{"@attributes"}->leafCount : '');
            $show_info[] = $info;
        }
        file_put_contents(APP_ROOT . '/public/content/tv_shows.json', json_encode($show_info));
    }
}