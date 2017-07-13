<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminController extends Controller {

  public function getHome(RequestInterface $request, ResponseInterface $response) {
    r($_SESSION);
    $this->render($response, 'admin/home.twig');
  }

  public function getSettings(RequestInterface $request, ResponseInterface $response) {
    $this->render($response, 'admin/settings.twig');
  }

  public function postSettings(RequestInterface $request, ResponseInterface $response) {
    $this->render($response, 'admin/settings.twig');
  }

}
