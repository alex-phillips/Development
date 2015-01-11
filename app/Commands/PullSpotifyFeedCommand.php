<?php

/**
 * Created by PhpStorm.
 * User: exonintrendo
 * Date: 9/28/14
 * Time: 9:46 AM
 */
class PullSpotifyFeedCommand extends \Primer\Console\Command\BaseCommand
{
    public function configure()
    {
        $this->setName('spotify:pull_feed');
        $this->setDescription("Pull Spotify feed from Last.FM based on provided username");
        $this->addArgument('username', \Primer\Console\Input\DefinedInput::VALUE_REQUIRED);
    }

    public function run()
    {
        $username = $this->args->getArgument('username');

        Log::info("Pulling Spotify feed for $username");

        $music_feed = array();

        $feed_url = "http://ws.audioscrobbler.com/1.0/user/$username/recenttracks.rss";
        $file_contents = file_get_contents($feed_url);
        $lastFM_results = new SimpleXMLElement($file_contents);

        $count = 0;

        foreach ($lastFM_results->channel->item as $song) {
            if ($count == 5) {
                break;
            }
            list($artist, $track) = explode(" – ", $song->title);
            $query = join("+", explode(" – ", $song->title));
            $query = str_replace(" ", "+", $song->title);

            $results = json_decode(file_get_contents("https://itunes.apple.com/search?term=" . $query . "&entity=song"));

            foreach ($results->results as $result) {
                if (strcasecmp($result->artistName, $artist) && strcasecmp($result->trackName, $track)) {
                    $music_feed[] = array(
                        'artist'        => $artist,
                        'track'         => $track,
                        'album_artwork' => $result->artworkUrl100
                    );
                    $count++;
                    break;
                }
                else {
                    if ($result->artistName == $artist && $result->trackName == $track) {
                        $music_feed[] = array(
                            'artist'        => $artist,
                            'track'         => $track,
                            'album_artwork' => $result->artworkUrl100
                        );
                        $count++;
                        break;
                    }
                }
            }
        }

        $music_feed = json_encode($music_feed);
        file_put_contents(APP_ROOT . '/public/content/' . $username . '_music_feed.json', $music_feed);
    }
}