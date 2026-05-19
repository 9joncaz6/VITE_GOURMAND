<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

/**
 * Render Free Hack :
 * Les variables d'environnement ne sont pas injectées correctement.
 * On les force ici pour garantir que Symfony les voit.
 */
putenv('MONGODB_URL=mongodb+srv://mayjoca789_db_user:n86DcKTrXa8QWAA1@cluster0.f9crcxj.mongodb.net/vitegourmand?retryWrites=true&w=majority&appName=Cluster0');
putenv('MONGODB_DB=vitegourmand');
putenv('DEFAULT_URI=https://vitegourmand.onrender.com'); // ← ajoute ton domaine ici

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
