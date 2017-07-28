<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class SystemController extends Controller {

  public function getInstall(RequestInterface $request, $response) {
    $envFile = dirname(dirname(__DIR__))."/.env";
    $migrateDir = dirname(dirname(__DIR__))."/public/installation";
    if(!file_exists($migrateDir)){
      $this->alert("Dossier 'public/installation' introuvable, impossible de procéder à l'installation");
      return $this->redirect($response, 'admin');
    }
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
        if($_POST['bdd']) $_SESSION['old']['bdd'] = $_POST['bdd'];
        if($_POST['port']) $_SESSION['old']['port'] = $_POST['port'];
        if($_POST['dbname']) $_SESSION['old']['dbname'] = $_POST['dbname'];
        if($_POST['bdd_user']) $_SESSION['old']['bdd_user'] = $_POST['bdd_user'];
        if($_POST['bdd_pass']) $_SESSION['old']['bdd_pass'] = $_POST['bdd_pass'];

        if($_POST['email']) $_SESSION['old']['email'] = $_POST['email'];
        if($_POST['password']) $_SESSION['old']['password'] = $_POST['password'];
        $this->alert('Impossible de se connecter à la base de données, re-vérifiez vos informations', 'danger');
        return $this->redirect($response, 'install', 400);
      };
    };

    $pdo = new \PDO($dsn, $user, $password);

    Validator::email()->validate($_POST['email']) || $errors['email'] = 'Votre devez entrer un email valide';
    if(empty($_POST['password'])) $errors['password'] = "Vous devez entrer un mot de passe";

    if(!empty($errors)){
      $this->alert($errors, 'errors');
      return $this->redirect($response, 'install', 400);
    }

    $envFile = dirname(dirname(__DIR__))."/.env";

    $config = "DBP_TYPE = 'mysql'\nDBP_NAME = '".$_POST['dbname']."'\nDBP_SERVER = '".$_POST['bdd']."'\nDBP_USER = '".$_POST['bdd_user']."'\nDBP_PWD = '".$_POST['bdd_pass']."'\nCACHE = true";

    if(!file_exists($envFile)){
      file_put_contents($envFile, $config);
    }

    $ch = curl_init();
    $url = "http://".$_SERVER['HTTP_HOST']."/installation/install.php";

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $result = curl_exec($ch);
    r($result);
    curl_close($ch);

    $pdo->query("INSERT INTO ranks SET name = 'admin'");
    $id_admin = $pdo->lastInsertId();
    $pdo->query("INSERT INTO ranks SET name = 'utilisateur'");

    $pdo->query("INSERT INTO settings SET upload_size = 1073741824");

    $req = $pdo->prepare("INSERT INTO users SET email = ?, password = ?, created_at = NOW(), id_rank = ?");
    $req->execute([$_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), $id_admin]);
    $id_user = $pdo->lastInsertId();

    $firstDirectory = dirname(dirname(__DIR__))."/public/directory/".$id_user;
    if(!file_exists($firstDirectory)){
      $this->new_directory($id_user);
    }

    $log = $pdo->prepare("INSERT INTO logs SET message = ?, ip = ?, date = NOW()");
    $log->execute(['Installation de files manager réussi', $this->get_ip()]);

    $this->alert("Félicitation, files manager as été correctement installé, pensez à supprimer le dossier 'public/installation'");
    return $this->redirect($response, 'login');

  }

  public function getUpdate(RequestInterface $request, $response) {

  }

  public function postUpdate(RequestInterface $request, $response) {

  }

}
