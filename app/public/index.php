<?php

/**
 * Primer PHP Framework
 *
 * @license http://opensource.org/licenses/MIT MIT License
 */

/**
 * Configuration for: Folders
 * There is no need to edit these unless the Primer folder is located in another
 * directory.
 */
define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', dirname(dirname(__FILE__)));
set_include_path(APP_ROOT);

//define('ENVIRONMENT', 'test');

/**
 * Configuration for: Timezone
 */
date_default_timezone_set('America/New_York');

require_once(APP_ROOT . '/../vendor/autoload.php');

$app = new \Primer\Core\Application();
$app->run();