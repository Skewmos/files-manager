<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class SystemController extends Controller {

  public function getInstall(RequestInterface $request, $response) {
    if(!isset($_SESSION['step'])){
      $_SESSION['step'] = 1;
      $params = array();
      $this->render($response, 'install.twig', $params);
    }else{
      if(isset($_SESSION['step2'])){

      }elseif(isset($_SESSION['step3'])){

      }
    }

  }

  public function postInstall(RequestInterface $request, $response) {

  }

  public function getUpdate(RequestInterface $request, $response) {

  }

  public function postUpdate(RequestInterface $request, $response) {

  }

}
