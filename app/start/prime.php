<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 1/4/15
 * Time: 9:29 AM
 */

$app['console']->addCommand(new \Primer\Console\Command\DemoCommand());
$app['console']->addCommand(new \Primer\Console\Command\DownCommand());
$app['console']->addCommand(new \Primer\Console\Command\UpCommand());
//
//$console->addCommand(new BuildPlexCommand());
$app['console']->addCommand(new PullSpotifyFeedCommand());
$app['console']->addCommand(new BuildJsCommand());
$app['console']->addCommand(new BuildCssCommand());
//$console->addCommand(new MonitorCommand());
//$console->addCommand(new BackupDatabaseCommand());