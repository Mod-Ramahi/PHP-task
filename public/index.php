<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\Filesystemloader;

$loader = new FilesystemLoader([
    __DIR__.'/../src/view/templates',
    // __DIR__.'/../src/views',
]);

$twig = new Environment($loader, [
    'debug' => true,
    'auto_reload' => true,
]);
$request_uri = $_SERVER['REQUEST_URI'];
$request_uri = strtok($request_uri, '?');
$url = isset($_GET['url'])? rtrim($_GET['url'], '/' ) : 'index';
$url = filter_var($url, FILTER_SANITIZE_URL);
// Handle API endpoints
// if ($url === 'registerAPI') {
//     require_once __DIR__ . '/../routes.php'; 
//     exit; 
// }
// if ($url === 'loginAPI') {
//     require_once __DIR__ . '/../routes.php'; 
//     exit; 
// }


require_once __DIR__ . '/../routes.php';
?>