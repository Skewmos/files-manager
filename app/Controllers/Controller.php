<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class Controller {

    private $container;

    public function __construct($container) {
      $this->container = $container;
    }

    public function __get($name) {
      return $this->container->get($name);
    }

    public function alert($message, $type = "success") {
      if(!isset($_SESSION['alert'])){
        $_SESSION['alert'] = [];
      }
      return $_SESSION['alert'][$type] = $message;
    }

    public function render(ResponseInterface $response, $file, $params = []) {
      $this->container->view->render($response, $file, $params);
    }

    public function redirect(ResponseInterface $response, $name, $status = 302) {
      return $response->withStatus($status)->withHeader('Location', $this->router->pathFor($name));
    }

    public function str_random($length){
      $alphabet = "0123456789azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN";
      return substr(str_shuffle(str_repeat($alphabet, $length)), 0, $length);
    }

    // Fonction qui converti une valeur MO en Octets
    public function moConvert($value){
      $result = $value * 1048576;
      return $result;
    }

    // Fonction qui converti une valeur Go en Octets
    public function goConvert($value){
      $result = $value * 1073741824;
      return $result;
    }

    // Fonction qui converti une valeur Octets en Mo
    public function octetConvertToMo($value){
      $result = $value / 1048576;
      return $result;
    }

    // Fonction qui retourne la valeur en octet la taille d'upload max présent en base de données
    public function fileSizeConvert($value){
      if($value >= 1073741824){
        $result = $value / 1073741824;
        return round($result, 2).' Go';
      }else{
        $result = $value / 1048576;
        return round($result, 2).' Mo';
      }
    }

    public function getformats(){
      return array(
        'pdf', 'xls', 'csv', 'txt', 'odt', 'doc',
        'jpg', 'jpeg', 'png', 'bmp', 'gif', 'ico',
        'mp4', 'mkv', 'avi', 'wmv',
        'mp3', 'flac', 'ogg', 'wma',
        'zip', 'tar.gz', 'rar', 'gzip', 'iso'
      );
    }

    public function get_ip(){
      if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
        $ip  = $_SERVER['HTTP_CLIENT_IP'];
      }else{
        $ip = $_SERVER['REMOTE_ADDR'];
      }
     return $ip;
    }

    public function new_directory($name){
      if(!file_exists('directory/'.$name)){
        if(!mkdir('directory/'.$name, 0775, true)){
          $this->alert("Impossible de créer le répertoire, vérifier les permissions", 'danger');
        }else{
          fopen("directory/".$name."/.gitkeep", "w+");
        }
      }
    }

    public function clear_directory($name){
      $id = trim($name, "'");
      if(file_exists('directory/'.$id) && is_dir('directory/'.$id)){
        if($handle = opendir('directory/'.$id)){
          while(false !== ($entry = readdir($handle))){
            if($entry != "." && $entry != ".."){
              if(isset($entry)){
                unlink('directory/'.$id.'/'.$entry);
              }
            }
          }
        }
        closedir($handle);
      }else{
        $this->alert("Impossible de supprimer le contenu du répertoire, le répertoire n'existe pas", 'danger');
      }
    }

    function remove_directory($name){
      $id = trim($name, "'");
      if(file_exists('directory/'.$id)){
        if(!rmdir('directory/'.$id)){
          $this->alert("Impossible de supprimer le répertoire, vérifier les permissions", 'danger');
        }
      }
    }

    public function addLog($message) {
      $ip = $this->get_ip();
      $this->medoo->insert('logs', [
        'message' => $message,
        'ip' => $ip,
        'date' => date('Y-m-d H:i:s')
      ]);
    }

}
