<?php

// Force specific constants
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_DIR', 'public');
define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . PUBLIC_DIR);
define('RESOURCES_DIR', 'resources');

require dirname(__DIR__) . '/vendor/autoload.php';

// Build request and detect flush
$request = \SilverStripe\Control\HTTPRequestBuilder::createFromEnvironment();
// Default application
$kernel = new \LeKoala\MicroFramework\MicroKernel(BASE_PATH);
$app = new \SilverStripe\Control\HTTPApplication($kernel);
$response = $app->handle($request);
$response->output();
