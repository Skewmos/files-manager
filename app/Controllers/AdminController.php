<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class AdminController extends Controller {

  public function getLog(RequestInterface $request, ResponseInterface $response) {
    $params = array();

    $migrateDir = dirname(dirname(__DIR__))."/public/installation";
    if(file_exists($migrateDir)){
      $params['install'] = "Pensez à supprimer le dossier 'public/installation'";
    }

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
    $errors = array();
    if(isset($_POST['size']) && !empty($_POST['size'])){
      if(isset($_POST['format']) && !empty($_POST['format'])){
        $format = $_POST['format'];
      }else{
        $format = "mo";
      }
      Validator::intVal()->validate($_POST['size']) || $errors['size'] = 'La taille doit-être un nombre numérique';

      if(!empty($errors)){
        $this->alert($errors, 'errors');
        return $this->redirect($response, 'settings');
      }else{
        if($format === "mo"){
          $this->addLog("La taille max d'upload est passée à ".$_POST['size']."Mo");
          $this->medoo->update('settings', [
            "upload_size" => $this->moConvert($_POST['size'])
          ]);
        }elseif($format === "go"){
          $this->addLog("La taille max d'upload est passée à ".$_POST['size']."Go");
          $this->medoo->update('settings', [
            "upload_size" => $this->goConvert($_POST['size'])
          ]);
        }
        $this->alert('Paramètres enregistrés');
        return $this->redirect($response, 'settings');
      }

    }else{
      $this->alert('Aucune configuration n\'a été envoyé', 'danger');
      return $this->redirect($response, 'settings');
    }
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
    $params['title'] = "Créer un compte utilisateur";
    $this->render($response, 'admin/form_user.twig', $params);
  }

  public function postAddUser(RequestInterface $request, ResponseInterface $response) {
    $errors = array();
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

            $user_id = $this->medoo->id();

            $this->new_directory($user_id);

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
    $params = array();
    $id = $request->getAttribute('id');
    if(Validator::intVal()->validate($id)){
      $search = $this->medoo->select('users', "*",[
        "id" => $id
      ]);

      if(!empty($search)){
        $params['ranks'] = $this->medoo->select('ranks', '*');
        $params['title'] = "Editer le compte ".$search[0]['email'];
        $params['user'] = $search[0];
        $this->render($response, 'admin/form_user.twig', $params);
      }else{
        $this->alert("Utilisateur non trouvé", 'danger');
        return $this->redirect($response, 'add_user');
      }

    }else{
      $this->alert("Id utilisateur non valide", 'danger');
      return $this->redirect($response, 'add_user');
    }
  }

  public function postEditUser(RequestInterface $request, ResponseInterface $response) {
    if(isset($_POST['id']) && !empty($_POST['id'])){
      $id = $_POST['id'];

      if(Validator::intVal()->validate($id)){
        $user = $this->medoo->select("users", "*",[
          "id" => $id
        ]);

        if(!empty($user)){
          $errors = array();
          if(isset($_POST['email']) && !empty($_POST['email'])){
            Validator::email()->validate($_POST['email']) || $errors['email'] = 'L\'email est invalide';
          }else{
            $errors['email'] = 'Un email doit être donné';
          }

          // On continue si l'email est valide
          if(empty($errors)){

            // On vérifie si aucun autre utilisateur ne possède cet email

            $search = $this->medoo->select('users', [
              'id', 'email'
            ],[
              'email' => $_POST['email'],
              'id[!]' => $id
            ]);

            // Si c'est le cas, on stop
            if(!empty($search)){
              $errors['email'] = 'Cet email est déjà pris';
              $this->alert($errors, 'errors');
              return $this->redirect($response, 'add_user', 400);
            }else{
              // Si email non trouvé, continuer

              $this->medoo->update('users', [
                'email' => $_POST['email']
              ],[
                'id' => $id
              ]);

              if(isset($_POST['rank']) && !empty($_POST['rank'])){
                $rank_id = $_POST['rank'];
              }else{
                $rank = $this->medoo->select('ranks', "*", [
                  'name' => 'utilisateur'
                ]);
                $rank_id = $rank[0]['id'];
              }

              $this->medoo->update('users', [
                'id_rank' => $rank_id
              ],[
                'id' => $id
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
                    'id' => $id
                  ]);

                  $this->addLog("Le compte ".$user[0]['email']." a été modifié");
                  $this->alert('Le compte '.$user[0]['email'].' a bien été modifié');
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
                 $errors['password'] = 'Vous n\'avez pas confirmé le mot de passe';
                 $this->alert($errors, 'errors');
                 return $this->redirect($response, 'add_user');
              }
              $this->addLog("Le compte ".$user[0]['email']." a été modifié");
              $this->alert('Le compte '.$user[0]['email'].' a bien été modifié');
              return $this->redirect($response, 'users');
            }

          }else{
            // On stop si l'email est invalide
            $this->alert($errors, 'errors');
            return $this->redirect($response, 'add_user', 400);
          }
        }else{
          $this->alert('Utilisateur non trouvé', "danger");
          return $this->redirect($response, 'users');
        }
      }else{
        $this->alert('Utilisateur non trouvé', "danger");
        return $this->redirect($response, 'users');
      }
    }else{
      $this->alert('Aucun utilisateur n\'est selectionné', "danger");
      return $this->redirect($response, 'users');
    }

  }

  public function getDelUser(RequestInterface $request, ResponseInterface $response) {
    $id = $request->getAttribute('id');
    if(Validator::intVal()->validate($id)){
      $search = $this->medoo->select("users", "*",[
        "id" => $id
      ]);
      if(!empty($search)){
        $this->medoo->delete("users", [
          "id" => $id
        ]);

        $this->clear_directory($id);
        $this->remove_directory($id);
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
