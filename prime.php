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
$console->dispatch();