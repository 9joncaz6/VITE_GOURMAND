<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

$env = getenv('APP_ENV') ?: 'prod';
$debug = getenv('APP_DEBUG') === '1';

if ($env !== 'prod') {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
