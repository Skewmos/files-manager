<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class AdminController extends Controller {

  public function getLog(RequestInterface $request, ResponseInterface $response) {
    $params = array();
    $logs = $this->medoo->select('logs', '*', [
      "ORDER" => ["date" => "DESC"],
      "LIMIT" => 15
    ]);
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
    $users = $this->medoo->select('users', [
      "[><]ranks" => ["id_rank" => "id"]
    ],[
      "users.id", "users.email", "ranks.name"
    ]);
    $params['users'] = $users;
    $this->render($response, 'admin/users.twig', $params);
  }

  public function getAddUser(RequestInterface $request, ResponseInterface $response) {
    $params = array();
    $params['ranks'] = $this->medoo->select('ranks', '*');
    $this->render($response, 'admin/add_user.twig', $params);
  }

  public function postAddUser(RequestInterface $request, ResponseInterface $response) {
    $errors = [];
    if(isset($_POST['email']) && !empty($_POST['email'])){
      Validator::email()->validate($_POST['email']) || $errors['email'] = 'L\'email est invalide';
    }else{
      $errors['email'] = 'Un email doit être donné';
    }

    // On continue si l'email est valide
    if(empty($errors)){

      // On vérifie si aucun autre utilisateur ne possède cet email
      $search = $this->medoo->select('users', [
        'email'
      ],[
        'email' => $_POST['email']
      ]);

      // Si c'est le cas, on stop
      if(!empty($search)){
        $errors['email'] = 'Cet email est déjà pris';
        $this->alert($errors, 'errors');
        return $this->redirect($response, 'add_user', 400);
      }else{
        // Si email non trouvé, continuer

        // On vérifie si l'utilisateur souhaite changer de mot de passe
        if(isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['password_confirm']) && !empty($_POST['password_confirm'])){

          // Si les 2 champs sont rempli, faire la vérification et enregistrer
          $passwordPost = $_POST['password'];
          $passwordConfirmPost = $_POST['password_confirm'];

          // Si les 2 champs sont identiques, enregistrer le mot de passe
          if($passwordPost === $passwordConfirmPost) {
            $password = password_hash($passwordPost, PASSWORD_DEFAULT);

            if(isset($_POST['rank']) && !empty($_POST['rank'])){
              $rank_id = $_POST['rank'];
            }else{
              $rank = $this->medoo->select('ranks', "*", [
                'name' => 'utilisateur'
              ]);
              $rank_id = $rank[0]['id'];
            }

            $this->medoo->insert('users', [
              'email' => $_POST['email'],
              'password' => $password,
              'id_rank' => $rank_id
            ]);

            $this->addLog("Le compte ".$_POST['email']." a été créé");
            $this->alert('Le compte utilisateur a bien été créé');
            return $this->redirect($response, 'users');
          }else{
            // Si les 2 champs ne sont pas identique, afficher l'erreur
            $errors['password'] = 'Votre mot de passe n\'est pas identique au mot de passe de confirmation';
            $this->alert($errors, 'errors');
            return $this->redirect($response, 'add_user');
          }

        }elseif( ( (isset($_POST['password']) && !empty($_POST['password'])) && (!isset($_POST['password_confirm']) || empty($_POST['password_confirm'])) )
         || ( (!isset($_POST['password']) && empty($_POST['password'])) && (isset($_POST['password_confirm']) || !empty($_POST['password_confirm'])) ) ){
           // Si l'un des 2 champs n'est pas rempli, afficher l'erreur
           $errors['password'] = 'Vous n\'avez pas confirmé votre mot de passe';
           $this->alert($errors, 'errors');
           return $this->redirect($response, 'add_user');
        }else{
          // Si les 2 champs ne sont pas rempli, afficher l'erreur
          $errors['password'] = 'Vous n\'avez pas entré de mot de passe';
          $this->alert($errors, 'errors');
          return $this->redirect($response, 'add_user', 400);
        }
      }

    }else{
      // On stop si l'email est invalide
      $this->alert($errors, 'errors');
      return $this->redirect($response, 'add_user', 400);
    }

  }

  public function getEditUser(RequestInterface $request, ResponseInterface $response) {
    $id = $request->getAttribute('id');
  }

  public function postEditUser(RequestInterface $request, ResponseInterface $response) {

  }

  public function getDelUser(RequestInterface $request, ResponseInterface $response) {
    $id = intval($request->getAttribute('id'));
    if($id != 0){
      $search = $this->medoo->select("users", "*",[
        "id" => $id
      ]);
      if(!empty($search)){
        $this->medoo->delete("users", [
          "id" => $id
        ]);
        $this->addLog("Le compte ".$search[0]['email']." a été supprimé");
        $this->alert('Le compte utilisateur a bien été supprimé');
        return $this->redirect($response, 'users');
      }else{
        $this->alert('Utilisateur non trouvé', "danger");
        return $this->redirect($response, 'users');
      }

    }else{
      $this->alert('Id utilisateur non valide', "danger");
      return $this->redirect($response, 'users');
    }
  }



  public function getDirectory(RequestInterface $request, ResponseInterface $response) {
    $params = array();
    $this->render($response, 'admin/directory.twig', $params);
  }

  public function postDirectory(RequestInterface $request, ResponseInterface $response) {

  }

}
