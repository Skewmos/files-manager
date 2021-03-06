<?php
namespace App\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;

class AuthMiddleware {

  private $twig;
  private $container;

  public function __construct(\Twig_Environment $twig, $container) {
    $this->twig = $twig;
    $this->container = $container;
  }

  public function __invoke(Request $request, Response $response, $next) {
    if (!isset($_SESSION['auth']) || empty($_SESSION['auth'])) {

      // Si cookie présent, alors vérifier
      if(isset($_COOKIE['remember']) && !empty($_COOKIE['remember'])){
        $remember_token = $_COOKIE['remember'];
        $parts = explode('==', $remember_token);
        $user_id = $parts[0];
        $user = $this->container->medoo->select("users", "*", [
        	"id" => $user_id
        ]);

        // Si utilisateur trouvé, vérifier le token
        if(!empty($user[0])){
            $expected = $user_id . '==' . $user[0]['remember_token'] . sha1($user_id . 'adtr');

            // Si token valide, initialiser la session
            if($expected == $remember_token){

              // Récupération du rank utilisateur
              if($user[0]['id_rank'] != null){
                $ranks = $this->container->medoo->select("ranks", "name", [
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

              setcookie('remember', $remember_token, time() + 60 * 60 * 24 * 1); // Cookie d'une durée de vie d'un jour
              return $response->withRedirect($this->container->router->pathFor('home'));
            }else{
              // Si cookie non valide, rediriger vers login
              return $response->withRedirect($this->container->router->pathFor('login'));
            }
        }else{
            // Si cookie mais utilisateur inexistant, expiration du cookie et rediriger vers login
            setcookie('remember', NULL, -1);
            return $response->withRedirect($this->container->router->pathFor('login'));
        }
      }else{
        // Si aucun cookie, rediriger vers login
        return $response->withRedirect($this->container->router->pathFor('login'));
      }

    }else{
      $this->twig->addGlobal('auth', isset($_SESSION['auth']) ? $_SESSION['auth'] : []);
    }

    return $next($request, $response);
  }

}
