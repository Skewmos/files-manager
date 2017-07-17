<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UploadController extends Controller
{

  public function getUpload(RequestInterface $request, $response)
  {
    $params = array();
    if(isset($_SESSION['upload_progress_uploadform'])){
      $_SESSION['upload_progress_uploadform']['bytes_processed'] = 0;
    }

    $size = $this->medoo->select('settings', 'upload_size');
    $params['upload_size'] = $size[0];
    $params['mo_size'] = $this->octetConvertToMo($size[0]);

    $formats = $this->getformats();
    $formats = implode(", ", $formats);
    $params['formats'] = $formats;

    $params['id_upload'] = ini_get("session.upload_progress.name");
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

  public function getUploadProgress(RequestInterface $request, $response)
  {
    if (isset($_SESSION["upload_progress_uploadform"]) && !empty($_SESSION["upload_progress_uploadform"])) {
      $current = $_SESSION["upload_progress_uploadform"]["bytes_processed"];
      $total = $_SESSION["upload_progress_uploadform"]["content_length"];
      echo $current < $total ? ceil($current / $total * 100) : 100;
    }
    else {
      echo 100;
    }
  }

}
