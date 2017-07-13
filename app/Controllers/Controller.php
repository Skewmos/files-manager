<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Controller {

    private $container;

    public function __construct($container) {
      $this->container = $container;
    }

    public function __get($name) {
      return $this->container->get($name);
    }

    public function alert($message, $type = "success") {
      if(!isset($_SESSION['alert'])){
        $_SESSION['alert'] = [];
      }
      return $_SESSION['alert'][$type] = $message;
    }

    public function render(ResponseInterface $response, $file, $params = []) {
      $this->container->view->render($response, $file, $params);
    }

    public function redirect(ResponseInterface $response, $name, $status = 302) {
      return $response->withStatus($status)->withHeader('Location', $this->router->pathFor($name));
    }

    public function str_random($length){
      $alphabet = "0123456789azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN";
      return substr(str_shuffle(str_repeat($alphabet, $length)), 0, $length);
    }

}
