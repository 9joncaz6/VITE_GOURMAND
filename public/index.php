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
putenv('DEFAULT_URI=https://vitegourmand.onrender.com');
putenv('DATABASE_URL=mysql://jonca:1PaxAugusta5@mysql-jonca.alwaysdata.net:3306/jonca_vitegourmand?serverVersion=mariadb-11.4.0&charset=utf8mb4');

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
