<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class AuthController extends Controller {


  public function getLogin(RequestInterface $request, ResponseInterface $response) {
    if (isset($_SESSION['auth']) && !empty($_SESSION['auth'])) {
      // Si session existante, rediriger vers home
      return $this->redirect($response, 'home');
    } else {
      $this->render($response, 'login.twig');
    }
  }

  public function postLogin(RequestInterface $request, ResponseInterface $response) {
    if (isset($_SESSION['auth']) && !empty($_SESSION['auth'])) {
      // Si session existante, rediriger vers home
      return $this->redirect($response, 'home');

    } elseif(!empty($_POST) && isset($_POST['email']) && isset($_POST['password'])){
      $errors = array();
      Validator::email()->validate($request->getParam('email')) || $errors['email'] = 'Votre email est invalide';

      // Si pas d'erreur de champ email
      if(empty($errors)){

        // Si POST, vérifier en base de données
        $user = $this->medoo->select("users", "*", [
          "email" => $_POST['email']
        ]);

        if(!empty($user[0])){
          // Si utilisateur trouvé, vérifier le mot de passe
          if(password_verify($_POST['password'], $user[0]['password'])){
            // Si mot de passe correct, initialiser la session

            if(isset($_POST['remember']) && intval($_POST['remember']) == 1){
              // Si le checkbox remember est cocher, initialiser le cookie

              $remember_token = $this->str_random(250);
              $this->medoo->update("users", [
                "remember_token" => $remember_token
              ],[
                "id" => $user[0]['id']
              ]);
              setcookie('remember', $user[0]['id'] . '==' . $remember_token . sha1($user[0]['id'] . 'adtr'), time() + 60 * 60 * 24 * 7);

            }

            // Récupération du rank utilisateur
            if($user[0]['id_rank'] != null){
              $ranks = $this->medoo->select("ranks", "name", [
                "id" => $user[0]['id_rank']
              ]);
              $rank = $ranks[0];
            }else{
              $rank = null;
            }

            // Initialisation de la session
            $_SESSION['auth'] = array(
              "id" => $user[0]['id'],
              "email" => $user[0]['email'],
              "created_at" => $user[0]['created_at'],
              "rank" => $rank
            );

            $this->addLog($user[0]['email']." s'est connecté");

            return $this->redirect($response, 'home');
          }else{
            // Mauvais mot de passe
            $errors['password'] = "Mot de passe incorrect";
            $this->alert($errors, 'errors');
            return $this->redirect($response, 'login', 400);
          }
        }else{
          // Utilisateur inexistant
          $errors['email'] = "L'email est inexistant";
          $this->alert($errors, 'errors');
          return $this->redirect($response, 'login');
        }
      }else{
        // Si email invalide
        $this->alert($errors, 'errors');
        return $this->redirect($response, 'login', 400);
      }

    }
  }

  public function getLogout(RequestInterface $request, ResponseInterface $response) {
    if (isset($_SESSION['auth']) && !empty($_SESSION['auth'])) {
      unset($_SESSION['auth']);
      unset($_SESSION['csrf']);
      setcookie('remember', NULL, -1);
      return $this->redirect($response, 'login');
    }else{
      return $this->redirect($response, 'login');
    }
  }

}
