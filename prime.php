#!/usr/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: exonintrendo
 * Date: 9/23/14
 * Time: 6:30 PM
 */

require_once('./app/Config/start.php');

$programName = <<<__TEXT__
__________        .__
\______   \_______|__| _____   ___________
 |     ___/\_  __ \  |/     \_/ __ \_  __ \
 |    |     |  | \/  |  Y Y  \  ___/|  | \/
 |____|     |__|  |__|__|_|  /\___  >__|
                           \/     \/
<info>Prime</info> version <warning>0.1</warning>
__TEXT__;

$console = new \Primer\Console\Console($programName);
$console->addCommand(new \Primer\Console\DownCommand());
$console->addCommand(new \Primer\Console\UpCommand());

$console->addCommand(new BuildPlexCommand());
$console->addCommand(new PullSpotifyFeedCommand());
$console->addCommand(new BuildJsCommand());
$console->addCommand(new MonitorCommand());
$console->addCommand(new BackupDatabaseCommand());

$console->run();