<?php

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// initializing class autoloader
require __DIR__.'/../vendor/autoload.php';

use App\Kernel;
use Dotenv\Dotenv;

// loading .env file variables
$dotenv = Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

// creating and running web application
$app = Kernel::createApp();
$app->run();
