<?php
namespace App\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;

class Middleware {

  private $container;

  public function __construct($container) {
    $this->container = $container;
  }

}
