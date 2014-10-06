<?php

/**
 * THIS IS THE CONFIGURATION FILE
 *
 * For more info about constants please @see http://php.net/manual/en/function.define.php
 * If you want to know why we use "define" instead of "const" @see http://stackoverflow.com/q/2447791/1114320
 */

/**
 * Site-specific values
 */
define('LOG_PATH', APP_ROOT . '/logs/');

return array(

    /*
     * Site name - can be used throughout the framework to reference
     */
    'site_name'       => 'Primer',

    /*
     * Enviroment - this determines which database
     */
    'environment'     => 'test',

    /*
     * Debug - determines whether to show debug messages or not
     */
    'debug'           => true,

    /*
     * Log path
     */
    'log_path'        => APP_ROOT . '/logs/',

    'logfile'         => 'primer.log',

    'log_daily_files' => true,

);