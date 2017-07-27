<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UploadDownloadController extends Controller
{

  public function getUpload(RequestInterface $request, $response)
  {
    $params = array();
    $size = $this->medoo->select('settings', 'upload_size');
    $params['upload_size'] = $size[0];
    $params['mo_size'] = $this->octetConvertToMo($size[0]);

    $formats = $this->getformats();
    $formats = implode(", ", $formats);
    $params['formats'] = $formats;

    $this->render($response, 'pages/upload.twig', $params);
  }

  public function postUpload(RequestInterface $request, $response)
  {
    if(!empty($_FILES['file']['name'])){
      $size = $this->medoo->select('settings', 'upload_size');
      $maxUploadSize = $size[0];

      if($_FILES["file"]["size"] > $maxUploadSize){
        $this->alert('Le fichier est trop grand', 'danger');
        return $this->redirect($response, 'upload');
      }else{
        $file_name = $_FILES['file']['name'];
        $size_file = $_FILES["file"]["size"];
        $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        if(in_array($extension, $this->getformats())){
          $id_user = $_SESSION['auth']['id'];
          $this->medoo->insert('files',[
            'name' => $file_name,
            'weight' => $this->fileSizeConvert($size_file),
            'format' => $extension,
            'id_user' => $id_user
          ]);

          $id_file = $this->medoo->id();

          $target_file = "directory/".$id_user."/".$id_file.".".$extension;
          move_uploaded_file($_FILES['file']["tmp_name"], $target_file);

          $this->addLog($_SESSION['auth']['email']." à uploadé un fichier nommé ".$file_name);
          $this->alert('Le fichier a bien été uploadé');
          return $this->redirect($response, 'home');
        }else{
          $this->alert('Le format du fichier n\'est pas autorisé', 'danger');
          return $this->redirect($response, 'upload');
        }

      }
    }
  }

  public function getDownloadUser(RequestInterface $request, $response) {
    $file_id = $request->getAttribute('file');
    $user_id = $request->getAttribute('user');

    $path = dirname(dirname(__DIR__))."/public/directory/".$user_id."/";
    if(!file_exists($path)){
      $this->alert('Répertoire introuvable', 'danger');
      return $this->redirect($response, 'home');
    }

    if($user_id != $_SESSION['auth']['id']){
      $access = $this->medoo->select('access', '*',[
        'id_dir' => $user_id,
        'type' => 'user',
        'id_user' => $_SESSION['auth']['id']
      ]);
      if(empty($access)){
        $this->alert('Vous n\'avez pas accès à ce répertoire', 'danger');
        return $this->redirect($response, 'home');
      }
    }

    $file = $this->medoo->select('files', '*',[
      'id' => $file_id,
      'id_user' => $user_id
    ]);

    if(!empty($file)){
      $location = $path.$file_id.".".$file[0]['format'];

      header("Cache-Control: public");
      header("Content-Description: File Transfer");
      header('Content-Type: application/octet-stream');
      header('Content-Transfer-Encoding: binary');
      header('Content-Length: ' . filesize($location));
      header('Content-disposition: attachment; filename="' . $file[0]['name'] . '"');
      readfile($location);

    }else{
      $this->alert('Fichier introuvable', 'danger');
      return $this->redirect($response, 'home');
    }
  }

  public function getDownloadDir(RequestInterface $request, $response) {
    $file_id = $request->getAttribute('file');
    $user_id = $request->getAttribute('dir');

  }

  public function getSteam(RequestInterface $request, $response) {
    $file_id = $request->getAttribute('file');
    $user_id = $request->getAttribute('user');

    $url = "http://".$_SERVER['HTTP_HOST'];
    $path = dirname(dirname(__DIR__))."/public/directory/".$user_id."/";
    if(!file_exists($path)){
      $this->alert('Répertoire introuvable', 'danger');
      return $this->redirect($response, 'home');
    }

    if($user_id != $_SESSION['auth']['id']){
      $access = $this->medoo->select('access', '*',[
        'id_dir' => $user_id,
        'type' => 'user',
        'id_user' => $_SESSION['auth']['id']
      ]);
      if(empty($access)){
        $this->alert('Vous n\'avez pas accès à ce répertoire', 'danger');
        return $this->redirect($response, 'home');
      }
    }

    $file = $this->medoo->select('files', '*',[
      'id' => $file_id,
      'id_user' => $user_id
    ]);

    if(!empty($file)){
      if($file[0]['format'] == "webm" || $file[0]['format'] == "mp4"){
        // Video
        $params = array();
        $params['format'] = "video";
        $params['file']['name'] = $file[0]['name'];
        $params['file']['format'] = $file[0]['format'];
        $params['location'] = $url."/directory/".$user_id."/".$file[0]['id'].".".$file[0]['format'];
        $this->render($response, 'pages/stream.twig', $params);
      }elseif($file[0]['format'] == "mp3"){
        // Audio
        $params = array();
        $params['format'] = "audio";
        $params['file']['name'] = $file[0]['name'];
        $params['file']['format'] = $file[0]['format'];
        $params['location'] = $url."/directory/".$user_id."/".$file[0]['id'].".".$file[0]['format'];
        $this->render($response, 'pages/stream.twig', $params);
      }else{
        $this->alert('Format de fichier non supporté', 'danger');
        return $this->redirect($response, 'home');
      }
    }else{
      $this->alert('Fichier introuvable', 'danger');
      return $this->redirect($response, 'home');
    }
  }

}
