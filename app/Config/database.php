<?php
/**
 * Configuration for: Database
 *
 * This is the place where you define your database credentials, type etc. The
 * environment in use is set in the default config.php (or matching domain config).
 */
return array(

    'production' => array(
        'db_type'  => 'mysql',
        'host'     => '127.0.0.1',
        'login'    => 'root',
        'password' => 'root',
        'database' => 'primer',
    ),

    'test'       => array(
        'db_type'  => 'mysql',
        'host'     => 'localhost',
        'login'    => 'root',
        'password' => 'root',
        'database' => 'primer',
    ),

);
