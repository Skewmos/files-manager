<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminController extends Controller {

  public function getLog(RequestInterface $request, ResponseInterface $response) {
    $params = array();
    $logs = $this->medoo->select('logs', '*', [
      "ORDER" => ["date" => "DESC"],
      "LIMIT" => 15
    ]);
    r($logs);
    $params['logs'] = $logs;
    $this->render($response, 'admin/logs.twig', $params);
  }

  public function getSettings(RequestInterface $request, ResponseInterface $response) {
    $params = array();
    $size = $this->medoo->select('settings', 'upload_size');
    $params['upload_size'] = $this->octetConvertToMo($size[0]);
    $this->render($response, 'admin/settings.twig', $params);
  }

  public function postSettings(RequestInterface $request, ResponseInterface $response) {

  }

  public function getUsers(RequestInterface $request, ResponseInterface $response) {
    $params = array();
    $this->render($response, 'admin/users.twig', $params);
  }

  public function postUsers(RequestInterface $request, ResponseInterface $response) {

  }

  public function getDirectory(RequestInterface $request, ResponseInterface $response) {
    $params = array();
    $this->render($response, 'admin/directory.twig', $params);
  }

  public function postDirectory(RequestInterface $request, ResponseInterface $response) {

  }

}
