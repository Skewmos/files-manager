<?php
namespace App\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;

class SessionMiddleware {

  private $container;

  public function __construct($container) {
    $this->container = $container;
  }

  public function __invoke(Request $request, Response $response, $next) {
    if (!isset($_SESSION['auth']) || empty($_SESSION['auth'])) {
      return $response->withRedirect($this->container->router->pathFor('login'));
    }

    $response = $next($request, $response);
    return $response;
  }

}
