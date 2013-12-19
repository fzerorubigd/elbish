#!/usr/bin/env php
<?php
date_default_timezone_set('UTC');

set_time_limit(0);

$autoloader = include (__DIR__ . "/../vendor/autoload.php");

$elbish = Cybits\Elbish\Application::createInstance();
$elbish->run();