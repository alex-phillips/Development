<?php
/**
 * Created by PhpStorm.
 * User: exonintrendo
 * Date: 9/23/14
 * Time: 6:30 PM
 */

require_once('./app/Config/start.php');

$console = new \Primer\Console\Console('Prime', '0.1');
$console->addCommand(new \Primer\Console\DownCommand(), array('down'));
$console->addCommand(new \Primer\Console\UpCommand(), array('up'));

$console->addCommand(new BuildPlexCommand(), array('plex:build'));
$console->addCommand(new PullSpotifyFeedCommand(), array('spotify:pull_feed'));
$console->addCommand(new BuildJsCommand(), array('js:build'));

$console->run();