<?php

/**
 * Primer PHP Framework
 *
 * @license http://opensource.org/licenses/MIT MIT License
 */

define('ROOT', dirname(dirname(dirname(__FILE__))));

$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
if (file_exists(ROOT . '/app/Config/' . $domain . '.php')) {
    require_once(ROOT . '/app/Config/' . $domain . '.php');
}
else {
    require_once(ROOT . '/app/Config/config.php');
}

require_once(ROOT . '/vendor/autoload.php');

$app = new \Primer\Core\Application();
$app->run();