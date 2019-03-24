<?php
require("vendor/autoload.php");
require("config/bootstrap.php");
session_start();

$loader= new Twig_Loader_Filesystem("template/");
$twig= new Twig_Environment($loader, array('cache'=>false));


$twig->addGlobal('session', $_SESSION);

$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
        'logger' => [
            'name' => 'slim-app',
            'level' => Monolog\Logger::DEBUG,
            'path' => __DIR__ . '/../logs/app.log',
        ],
    ],
];
$app = new \Slim\App($config);



require("Controllers/UserController.php");
require("Controllers/ProductsController.php");

$app->get('/', accueil)->setName('accueil');
$app->get('/accueil', accueil)->setName('accueil');

function accueil($request, $response, $args){
    global $twig;
     global $entityManager;
     $products;
     
    
    $productRepository = $entityManager->getRepository('Product');
      $products=$productRepository->findBy(array(),array(), 5, 0);
    $params= array('title' => 'Accueil','products'=>$products,);
    $template= $twig -> loadTemplate('accueil.twig');
    
    return $response->write($template->render($params));
}

$loggedInMiddleware = function ($request, $response, $next) {
    $route = $request->getAttribute('route');
    $routeName = $route->getName();
    $groups = $route->getGroups();
    $methods = $route->getMethods();
    $arguments = $route->getArguments();

    # Define routes that user does not have to be logged in with. All other routes, the user
    # needs to be logged in with.
    $publicRoutesArray = array(
        'login',
        'validate',
        'register',
        'accueil',
        'checkLogin'
    );

    if (!isset(	$_SESSION['user_id'] ) && !in_array($routeName, $publicRoutesArray))
    {
        // redirect the user to the login page and do not proceed.
        $response = $response->withRedirect('/login');
    }
    else
    {
        // Proceed as normal...
        $response = $next($request, $response);
    }

    return $response;
};

$adminMiddleware = function ($request, $response, $next) {
    $route = $request->getAttribute('route');
    $routeName = $route->getName();
    $groups = $route->getGroups();
    $methods = $route->getMethods();
    $arguments = $route->getArguments();

    # Define routes that user does not have to be logged in with. All other routes, the user
    # needs to be logged in with.
    $adminRoutesArray = array(
        'manageProducts',
        'updateProduct'
    );

    if ((!isset(	$_SESSION['user_admin'] ) || 	$_SESSION['user_admin']==false) && in_array($routeName, $adminRoutesArray))
    {
        // redirect the user to the login page and do not proceed.
        $response = $response->withRedirect('/');
    }
    else
    {
        // Proceed as normal...
        $response = $next($request, $response);
    }

    return $response;
};

// Apply the middleware to every request.
$app->add($loggedInMiddleware);
$app->add($adminMiddleware);
$app -> run();
?>