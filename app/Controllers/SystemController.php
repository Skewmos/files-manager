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
    $errors = array();
    $port = "";
    $password = "";
    if(empty($_POST['bdd'])) $errors['bdd'] = "Vous devez entrer une adresse serveur";
    if(!empty($_POST['port'])) $port = ";port=".$_POST['port'];
    if(empty($_POST['dbname'])) $errors['dbname'] = "Vous devez entrer un nom d'une base de données";
    if(empty($_POST['bdd_user'])) $errors['bdd_user'] = "Vous devez entrer un nom d'utilisateur";
    if(!empty($_POST['bdd_pass'])) $password = $_POST['bdd_pass'];

    if(!empty($errors)){
      $this->alert($errors, 'errors');
      return $this->redirect($response, 'install', 400);
    }

    $dsn = "mysql:dbname=".$_POST['dbname'].";host=".$_POST['bdd'].$port;
    $user = $_POST['bdd_user'];
    $password = $_POST['bdd_pass'];

    // En cas d'erreur de connexion
    $c = $this->container['container'];
    $c['errorHandler'] = function ($c) {
      return function ($request, $response, $methods) use ($c) {
        $_SESSION['old']['bdd'] = $_POST['bdd'];
        $_SESSION['old']['port'] = $_POST['port'];
        $_SESSION['old']['dbname'] = $_POST['dbname'];
        $_SESSION['old']['bdd_user'] = $_POST['bdd_user'];

        $_SESSION['old']['email'] = $_POST['email'];
        $_SESSION['old']['password'] = $_POST['password'];
        $this->alert('Impossible de se connecter à la base de données, re-vérifiez vos informations', 'danger');
        return $this->redirect($response, 'install', 400);
      };
    };

    $dbh = new \PDO($dsn, $user, $password);

    Validator::email()->validate($_POST['email']) || $errors['email'] = 'Votre devez entrer un email valide';
    if(empty($_POST['password'])) $errors['password'] = "Vous devez entrer un mot de passe";

    if(!empty($errors)){
      $this->alert($errors, 'errors');
      return $this->redirect($response, 'install', 400);
    }

    $phinx = dirname(dirname(__DIR__))."/vendor/bin/phinx";
    $envFile = dirname(dirname(__DIR__))."/.env";

    $config = "# Database production\nDBP_TYPE = 'mysql'\nDBP_NAME = '".$_POST['dbname']."'\nDBP_SERVER = '".$_POST['bdd']."'\nDBP_USER = '".$_POST['bdd_user']."'\nDBP_PWD = '".$_POST['bdd_pass']."'\n# Environment mode\nENV = 'prod'\n# Cache twig\nCACHE = true";

    if(!file_exists($envFile)){
      r("fichier créé");
      file_put_contents($envFile, $config);
    }

    exec($phinx." migrate", $output);
    r($output);

  }

  public function getUpdate(RequestInterface $request, $response) {

  }

  public function postUpdate(RequestInterface $request, $response) {

  }

}
