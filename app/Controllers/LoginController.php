<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class LoginController extends Controller {

  public function getLogin(RequestInterface $request, ResponseInterface $response) {
    $this->render($response, 'login.twig');
  }

  public function postLogin(RequestInterface $request, ResponseInterface $response) {
    //$this->render($response, 'login.twig');
  }

  public function getLogout(RequestInterface $request, ResponseInterface $response) {
    session_destroy();
    return $this->redirect($response, 'login');
  }

}
