<?php
require("vendor/autoload.php");
require("config/bootstrap.php");

$loader= new Twig_Loader_Filesystem("template/");
$twig= new Twig_Environment($loader, array('cache'=>false));

$config = [
    'settings' => [
        'displayErrorDetails' => true,

        'logger' => [
            'name' => 'slim-app',
            'level' => Monolog\Logger::DEBUG,
            'path' => __DIR__ . '/../logs/app.log',
        ],
    ],
];
$app = new \Slim\App($config);

require("Controllers/UserController.php");


$app->get('/', accueil);
$app->get('/accueil', accueil);

function accueil($request, $response, $args){
    global $twig;
    
    $params= array('title' => 'Acceuil');
    $template= $twig -> loadTemplate('accueil.twig');
    
    return $response->write($template->render($params));
}


$app -> run();
?>