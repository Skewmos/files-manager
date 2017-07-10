<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class AuthController extends Controller {


  public function passwordCrypt($password) {
    $keyHash = md5(self::$key);
    $key = substr($keyHash, 0, mcrypt_get_key_size(self::$cipher, self::$mode));
    $iv  = substr($keyHash, 0, mcrypt_get_block_size(self::$cipher, self::$mode));

    $password = mcrypt_encrypt(self::$cipher, $key, $data, self::$mode, $iv);

    return base64_encode($password);
  }

  public function loginCheck($datas){
    $datas = $this->medoo->select("posts", "*");
  }

  public function getLogin(RequestInterface $request, ResponseInterface $response) {
    if (isset($_SESSION['auth']) || !empty($_SESSION['auth'])) {
      return $this->redirect($response, 'home');
    } else {
      $this->render($response, 'login.twig');
    }
  }

  public function postLogin(RequestInterface $request, ResponseInterface $response) {
    if (isset($_SESSION['auth']) || !empty($_SESSION['auth'])) {
      return $this->redirect($response, 'home');
    } else {
      // $this->render($response, 'login.twig');
    }
  }

  public function getLogout(RequestInterface $request, ResponseInterface $response) {
    session_destroy();
    return $this->redirect($response, 'login');
  }

}
