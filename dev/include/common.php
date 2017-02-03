<?php
/**
 * These are common php actions and loading of libraries for all pages
 */
//ini_set('display_errors', 'On');

/** Load database configureation settings */
require_once("settings.php");

/** Load Mustache and set it up for use for all pages */
require_once("Mustache/Autoloader.php");
Mustache_Autoloader::register();

$m = new Mustache_Engine([
    'pragmas' => [Mustache_Engine::PRAGMA_BLOCKS],
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/../views'),
]);

/** Load MySQL database library and make it available for all scripts */
require_once('MysqliDb.php');
$db = new MysqliDb ($host, $username, $password, $dbname);

/** Load weather cache and adds it to the $data var for use in all pages */
require_once("weather.php");
$weather = outputWeather($db);
$weather['CurrentTemp'] = round($weather['CurrentTemp'], 1);
$data['weatherNav'] = $weather;

/** Check to see if user is Admin to add components using Mustache for Admin users to control */
if(isset($_SESSION['user'])) {
    if ($_SESSION['user']['admin'] == 1) {
        $data['admin'] = true;
    }
}

/** Set up the site to use local TimeZone */
date_default_timezone_set('America/Denver');
