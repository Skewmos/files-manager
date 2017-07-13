<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class HomeController extends Controller {

  public function getHome(RequestInterface $request, ResponseInterface $response) {
    r($_SESSION);
    if(isset($_SESSION['auth']) && !empty($_SESSION['auth'])){
      $this->render($response, 'pages/home.twig', ["auth" => $_SESSION['auth']]);
    }else{
      $this->render($response, 'pages/home.twig');
    }
  }

  public function postHome(RequestInterface $request, ResponseInterface $response) {
    $errors = [];
    Validator::email()->validate($request->getParam('email')) || $errors['email'] = 'Votre email est invalide';
    if(!empty($errors)){
      $this->alert($errors, 'errors');
      return $this->redirect($response, 'home', 400);
    }else{
      $this->alert('Les champs on été remplis correctement');
      return $this->redirect($response, 'home');
    }
  }
}
