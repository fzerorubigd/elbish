#!/usr/bin/env php
<?php
date_default_timezone_set('UTC');

set_time_limit(0);

/** @var $autoLoader \Composer\Autoload\ClassLoader */
$autoLoader = include (__DIR__ . "/../vendor/autoload.php");

$elbish = Cybits\Elbish\Application::createInstance($autoLoader);
$elbish->run();