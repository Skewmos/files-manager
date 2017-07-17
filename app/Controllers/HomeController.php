<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class HomeController extends Controller {

  public function getHome(RequestInterface $request, ResponseInterface $response) {
    r($_SESSION);
    $this->render($response, 'pages/home.twig');
  }

  public function getProfil(RequestInterface $request, ResponseInterface $response) {
    $this->render($response, 'pages/profil.twig');
  }

  public function postProfil(RequestInterface $request, ResponseInterface $response) {
    $errors = [];
    Validator::email()->validate($_POST['email']) || $errors['email'] = 'Votre email est invalide';

    // On continue si l'email est valide
    if(empty($errors)){

      // On vérifie si aucun autre utilisateur ne possède cet email
      $search = $this->medoo->select('users', '*',[
        'email' => $_POST['email'],
        'id[!]' => $_SESSION['auth']['id']
      ]);

      // Si c'est le cas, on stop
      if(!empty($search)){
        $errors['email'] = 'Cet email est déjà pris';
        $this->alert($errors, 'errors');
        return $this->redirect($response, 'profil', 400);
      }else{
        // Si email non trouvé, continuer

        $this->medoo->update('users', [
          'email' => $_POST['email']
        ],[
          'id' => $_SESSION['auth']['id']
        ]);

        // On vérifie si l'utilisateur souhaite changer de mot de passe
        if(isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['password_confirm']) && !empty($_POST['password_confirm'])){

          // Si les 2 champs sont rempli, faire la vérification et enregistrer
          $passwordPost = $_POST['password'];
          $passwordConfirmPost = $_POST['password_confirm'];

          // Si les 2 champs sont identiques, enregistrer le mot de passe
          if($passwordPost === $passwordConfirmPost) {
            $password = password_hash($passwordPost, PASSWORD_DEFAULT);

            $this->medoo->update('users', [
              'password' => $password
            ],[
              'id' => $_SESSION['auth']['id']
            ]);

            $this->alert('Votre compte as été mise à jour');
            return $this->redirect($response, 'profil');
          }else{
            // Si les 2 champs ne sont pas identique, afficher l'erreur
            $errors['password'] = 'Votre mot de passe n\'est pas identique au mot de passe de confirmation';
            $this->alert($errors, 'errors');
            return $this->redirect($response, 'profil');
          }

        }elseif( ( (isset($_POST['password']) && !empty($_POST['password'])) && (!isset($_POST['password_confirm']) || empty($_POST['password_confirm'])) )
         || ( (!isset($_POST['password']) && empty($_POST['password'])) && (isset($_POST['password_confirm']) || !empty($_POST['password_confirm'])) ) ){
           // Si l'un des 2 champs n'est pas rempli, afficher l'erreur
           $errors['password'] = 'Vous n\'avez pas confirmé votre mot de passe';
           $this->alert($errors, 'errors');
           return $this->redirect($response, 'profil');
        }else{
          // Si les 2 champs ne sont pas rempli, terminer
          $this->alert('Votre compte as été mise à jour');
          return $this->redirect($response, 'profil');
        }
      }

    }else{
      // On stop si l'email est invalide
      $this->alert($errors, 'errors');
      return $this->redirect($response, 'profil', 400);
    }

  }
}
