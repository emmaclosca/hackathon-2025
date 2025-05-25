<?php

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__.'/../vendor/autoload.php';

use App\Kernel;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$app = Kernel::createApp();
$app->run();
