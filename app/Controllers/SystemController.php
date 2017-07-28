<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class SystemController extends Controller {

  public function getInstall(RequestInterface $request, $response) {
    $params = array();
    $this->render($response, 'install.twig', $params);
  }

  public function postInstall(RequestInterface $request, $response) {

  }

  public function getUpdate(RequestInterface $request, $response) {

  }

  public function postUpdate(RequestInterface $request, $response) {

  }

}
