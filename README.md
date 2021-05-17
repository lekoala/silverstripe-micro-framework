# SilverStripe Micro Framework

[![Build Status](https://travis-ci.com/lekoala/silverstripe-micro-framework.svg?branch=master)](https://travis-ci.com/lekoala/silverstripe-micro-framework/)
[![scrutinizer](https://scrutinizer-ci.com/g/lekoala/silverstripe-micro-framework/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lekoala/silverstripe-micro-framework/)
[![Code coverage](https://codecov.io/gh/lekoala/silverstripe-micro-framework/branch/master/graph/badge.svg)](https://codecov.io/gh/lekoala/silverstripe-micro-framework)

## Intro

This module makes it easier to use standalone SilverStripe framework without the Cms module.

It can also run a website without any database configured.

WARNING : this is highly experimental :-)

## Replace your index.php

In order to use this, you need to replace the default index.php with this

```php
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
```

## New base controller

Please use MicroController as the base controller for your applications.
It is recommended to create a base controller (like a PageController) as a base
for your application instead of extending MicroController each time.

```php
class AppController extends MicroController
{
}
```

### Setting base controller

This is really handy for Security screen.

```yml
SilverStripe\Security\Security:
  page_class: 'App\AppController'
```

Note: this is working thanks to our custom MicroSecurity extension.

### Auto routing

If you define `url_segment` on your controllers, the will be added to available routes automatically

    class HomeController extends AppController
    {
        private static $url_segment = 'home';
        private static $is_home = true;
        ...

You can also set a `is_home` variable for the default controller. In this case, the default segment /home
will redirect to / to avoid duplicated urls.

### Simple action declaration

If it bothers you to declare two times your action (once as a function, and once as a function), fear not!

You can now simply make sure your first argument accepts an `HTTPRequest` and it will be considered as a valid action.

### Page compat

Most SilverStripe projects have a "Page" template. Even if you are not using Page class, the Page.ss will be seen
as the base class.

### Login as admin without db

There are some convenience function allowing you to use the login screen without db

## Compatibility

Tested with 4.6 and higher

## Maintainer

LeKoala - thomas@lekoala.be
