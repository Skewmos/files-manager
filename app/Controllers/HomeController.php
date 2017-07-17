<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class HomeController extends Controller {

  public function getHome(RequestInterface $request, ResponseInterface $response) {
    $params = array();
    $files = $this->medoo->select("files", "*",[
      "id_user" => $_SESSION['auth']['id']
    ]);
    $params['files'] = $files;
    $this->render($response, 'pages/home.twig', $params);
  }

  public function getProfil(RequestInterface $request, ResponseInterface $response) {
    $this->render($response, 'pages/profil.twig');
  }

  public function postProfil(RequestInterface $request, ResponseInterface $response) {
    $errors = array();
    Validator::email()->validate($_POST['email']) || $errors['email'] = 'Votre email est invalide';

    // On continue si l'email est valide
    if(empty($errors)){

      // On vérifie si aucun autre utilisateur ne possède cet email
      $search = $this->medoo->select('users', [
        'email'
      ],[
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
          $this->alert('Votre compte a été mise à jour');
          return $this->redirect($response, 'profil');
        }
      }

    }else{
      // On stop si l'email est invalide
      $this->alert($errors, 'errors');
      return $this->redirect($response, 'profil', 400);
    }

  }

  public function getDelFile(RequestInterface $request, ResponseInterface $response) {
    $id = $request->getAttribute('id');
    if(Validator::intVal()->validate($id)){

      $file = $this->medoo->select('files', "*",[
        "id" => $id
      ]);

      if(!empty($file)){
        if(file_exists('directory/'.$file[0]['id_user'].'/'.$file[0]['id'].".".$file[0]['format'])){
          if(!unlink('directory/'.$file[0]['id_user'].'/'.$file[0]['id'].".".$file[0]['format'])){
            $this->alert("Impossible de supprimer le fichier, vérifier les permissions", 'danger');
            return $this->redirect($response, 'home');
          }else{
            $this->medoo->delete('files',[
              "id" => $id
            ]);
            $this->addLog("Le fichier ".$file[0]['name']." du répertoire de ".$_SESSION['auth']['email']." a été supprimé");
            $this->alert('Le fichier a bien été supprimé');
            return $this->redirect($response, 'home');
          }
        }else{
          $this->alert("Impossible de supprimer le fichier, fichier introuvable dans le répertoire", 'danger');
          return $this->redirect($response, 'home');
        }
      }else{
        $this->alert("Impossible de supprimer le fichier, fichier introuvable en base de données", 'danger');
        return $this->redirect($response, 'home');
      }

    }else{
      $this->alert('Id du fichier est invalide', "danger");
      return $this->redirect($response, 'home');
    }
  }
}
