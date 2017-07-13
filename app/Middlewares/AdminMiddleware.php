<?php
namespace App\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;

class AdminMiddleware {

  private $container;

  public function __construct($container) {
    $this->container = $container;
  }

  public function __invoke(Request $request, Response $response, $next) {

    if($_SESSION['auth']['rank'] != "admin"){
      // Si l'utilisateur de la session en cours n'est pas admin, rediriger vers le home
      return $response->withRedirect($this->container->router->pathFor('home'));
    }

    return $next($request, $response);
  }

}
